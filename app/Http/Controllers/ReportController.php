<?php

namespace App\Http\Controllers;

use App\Exports\ArrearsExport;
use App\Exports\BillingSummaryExport;
use App\Exports\CollectionsExport;
use App\Exports\ConsumerMasterlistExport;
use App\Exports\ConsumptionExport;
use App\Models\Bill;
use App\Models\Consumer;
use App\Models\MeterReading;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Check if user can access reports.
     */
    private function canAccessReport(string $reportType): bool
    {
        $role = Auth::user()->role->slug;

        // Admin can access all reports
        if ($role === 'admin') {
            return true;
        }

        // Cashier can access bill-related reports
        if ($role === 'cashier') {
            return in_array($reportType, ['collections', 'billing-summary']);
        }

        return false;
    }

    /**
     * Collections Report - Payment collections by date range.
     */
    public function collections(Request $request): View
    {
        if (! $this->canAccessReport('collections')) {
            abort(403);
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $payments = Payment::with(['consumer.user', 'processedBy', 'bill'])
            ->whereBetween('paid_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('paid_at')
            ->get();

        // Group by date for summary
        $byDate = $payments->groupBy(fn ($p) => $p->paid_at->toDateString());

        // Group by processor
        $byProcessor = $payments->groupBy('processed_by')->map(fn ($group) => [
            'processor' => $group->first()->processedBy,
            'total' => $group->sum('amount'),
            'count' => $group->count(),
        ]);

        return view('reports.collections', [
            'payments' => $payments,
            'byDate' => $byDate,
            'byProcessor' => $byProcessor,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAmount' => $payments->sum('amount'),
            'totalCount' => $payments->count(),
        ]);
    }

    /**
     * Export Collections Report.
     */
    public function collectionsExport(Request $request, string $format): BinaryFileResponse|\Illuminate\Http\Response
    {
        if (! $this->canAccessReport('collections')) {
            abort(403);
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $payments = Payment::with(['consumer.user', 'processedBy', 'bill'])
            ->whereBetween('paid_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('paid_at')
            ->get();

        $filename = 'collections-report-'.$startDate.'-to-'.$endDate;

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.exports.collections-pdf', [
                'payments' => $payments,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalAmount' => $payments->sum('amount'),
            ]);

            return $pdf->download($filename.'.pdf');
        }

        return Excel::download(
            new CollectionsExport($startDate, $endDate),
            $filename.'.xlsx'
        );
    }

    /**
     * Billing Summary Report - Bills overview by period.
     */
    public function billingSummary(Request $request): View
    {
        if (! $this->canAccessReport('billing-summary')) {
            abort(403);
        }

        $period = $request->get('period', now()->format('Y-m'));

        $bills = Bill::with(['consumer.user'])
            ->where('billing_period', $period)
            ->orderBy('consumer_id')
            ->get();

        $summary = [
            'total_billed' => $bills->sum('total_amount'),
            'total_collected' => $bills->sum('amount_paid'),
            'total_outstanding' => $bills->sum('balance'),
            'count_paid' => $bills->where('status', 'paid')->count(),
            'count_partial' => $bills->where('status', 'partial')->count(),
            'count_unpaid' => $bills->where('status', 'unpaid')->count(),
            'count_overdue' => $bills->where('status', 'overdue')->count(),
        ];

        // Available periods for dropdown
        $periods = Bill::select('billing_period')
            ->distinct()
            ->orderByDesc('billing_period')
            ->pluck('billing_period');

        return view('reports.billing-summary', [
            'bills' => $bills,
            'summary' => $summary,
            'period' => $period,
            'periods' => $periods,
        ]);
    }

    /**
     * Export Billing Summary Report.
     */
    public function billingSummaryExport(Request $request, string $format): BinaryFileResponse|\Illuminate\Http\Response
    {
        if (! $this->canAccessReport('billing-summary')) {
            abort(403);
        }

        $period = $request->get('period', now()->format('Y-m'));

        $bills = Bill::with(['consumer.user'])
            ->where('billing_period', $period)
            ->orderBy('consumer_id')
            ->get();

        $filename = 'billing-summary-'.$period;

        if ($format === 'pdf') {
            $summary = [
                'total_billed' => $bills->sum('total_amount'),
                'total_collected' => $bills->sum('amount_paid'),
                'total_outstanding' => $bills->sum('balance'),
            ];

            $pdf = Pdf::loadView('reports.exports.billing-summary-pdf', [
                'bills' => $bills,
                'summary' => $summary,
                'period' => $period,
            ]);

            return $pdf->download($filename.'.pdf');
        }

        return Excel::download(
            new BillingSummaryExport($period),
            $filename.'.xlsx'
        );
    }

    /**
     * Arrears Report - Outstanding balances.
     */
    public function arrears(Request $request): View
    {
        if (! $this->canAccessReport('arrears')) {
            abort(403);
        }

        $blockId = $request->get('block_id');

        $query = Consumer::with(['user', 'block', 'bills' => function ($q) {
            $q->where('balance', '>', 0);
        }])
            ->whereHas('bills', function ($q) {
                $q->where('balance', '>', 0);
            });

        if ($blockId) {
            $query->where('block_id', $blockId);
        }

        $consumers = $query->get()->map(function ($consumer) {
            $consumer->total_arrears = $consumer->bills->sum('balance');
            $consumer->oldest_unpaid = $consumer->bills->sortBy('billing_period')->first();

            return $consumer;
        })->sortByDesc('total_arrears');

        $blocks = \App\Models\Block::orderBy('name')->get();

        return view('reports.arrears', [
            'consumers' => $consumers,
            'blocks' => $blocks,
            'currentBlock' => $blockId,
            'totalArrears' => $consumers->sum('total_arrears'),
        ]);
    }

    /**
     * Export Arrears Report.
     */
    public function arrearsExport(Request $request, string $format): BinaryFileResponse|\Illuminate\Http\Response
    {
        if (! $this->canAccessReport('arrears')) {
            abort(403);
        }

        $blockId = $request->get('block_id');
        $filename = 'arrears-report-'.now()->format('Y-m-d');

        if ($format === 'pdf') {
            $query = Consumer::with(['user', 'block', 'bills' => function ($q) {
                $q->where('balance', '>', 0);
            }])
                ->whereHas('bills', function ($q) {
                    $q->where('balance', '>', 0);
                });

            if ($blockId) {
                $query->where('block_id', $blockId);
            }

            $consumers = $query->get()->map(function ($consumer) {
                $consumer->total_arrears = $consumer->bills->sum('balance');

                return $consumer;
            })->sortByDesc('total_arrears');

            $pdf = Pdf::loadView('reports.exports.arrears-pdf', [
                'consumers' => $consumers,
                'totalArrears' => $consumers->sum('total_arrears'),
            ]);

            return $pdf->download($filename.'.pdf');
        }

        return Excel::download(
            new ArrearsExport($blockId),
            $filename.'.xlsx'
        );
    }

    /**
     * Consumption Report - Monthly water usage.
     */
    public function consumption(Request $request): View
    {
        if (! $this->canAccessReport('consumption')) {
            abort(403);
        }

        $year = $request->get('year', now()->year);

        // Get monthly consumption data
        $monthlyData = MeterReading::selectRaw('billing_period, SUM(consumption) as total_consumption, COUNT(*) as reading_count, AVG(consumption) as avg_consumption')
            ->whereRaw('billing_period LIKE ?', [$year.'-%'])
            ->groupBy('billing_period')
            ->orderBy('billing_period')
            ->get();

        // Get top consumers for the year
        $topConsumers = MeterReading::with(['consumer.user'])
            ->selectRaw('consumer_id, SUM(consumption) as total_consumption')
            ->whereRaw('billing_period LIKE ?', [$year.'-%'])
            ->groupBy('consumer_id')
            ->orderByDesc('total_consumption')
            ->limit(10)
            ->get();

        // Available years
        $years = MeterReading::selectRaw('DISTINCT SUBSTRING(billing_period, 1, 4) as year')
            ->orderByDesc('year')
            ->pluck('year');

        return view('reports.consumption', [
            'monthlyData' => $monthlyData,
            'topConsumers' => $topConsumers,
            'year' => $year,
            'years' => $years,
            'totalConsumption' => $monthlyData->sum('total_consumption'),
        ]);
    }

    /**
     * Export Consumption Report.
     */
    public function consumptionExport(Request $request, string $format): BinaryFileResponse|\Illuminate\Http\Response
    {
        if (! $this->canAccessReport('consumption')) {
            abort(403);
        }

        $year = $request->get('year', now()->year);
        $filename = 'consumption-report-'.$year;

        if ($format === 'pdf') {
            $monthlyData = MeterReading::selectRaw('billing_period, SUM(consumption) as total_consumption, COUNT(*) as reading_count, AVG(consumption) as avg_consumption')
                ->whereRaw('billing_period LIKE ?', [$year.'-%'])
                ->groupBy('billing_period')
                ->orderBy('billing_period')
                ->get();

            $pdf = Pdf::loadView('reports.exports.consumption-pdf', [
                'monthlyData' => $monthlyData,
                'year' => $year,
                'totalConsumption' => $monthlyData->sum('total_consumption'),
            ]);

            return $pdf->download($filename.'.pdf');
        }

        return Excel::download(
            new ConsumptionExport($year),
            $filename.'.xlsx'
        );
    }

    /**
     * Consumer Masterlist - Full consumer registry.
     */
    public function consumerMasterlist(Request $request): View
    {
        if (! $this->canAccessReport('consumer-masterlist')) {
            abort(403);
        }

        $status = $request->get('status');
        $blockId = $request->get('block_id');

        $query = Consumer::with(['user', 'block']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($blockId) {
            $query->where('block_id', $blockId);
        }

        $consumers = $query->orderBy('id_no')->get();

        $blocks = \App\Models\Block::orderBy('name')->get();

        return view('reports.consumer-masterlist', [
            'consumers' => $consumers,
            'blocks' => $blocks,
            'currentStatus' => $status,
            'currentBlock' => $blockId,
            'totalActive' => Consumer::where('status', 'Active')->count(),
            'totalDisconnected' => Consumer::where('status', 'Disconnected')->count(),
        ]);
    }

    /**
     * Export Consumer Masterlist.
     */
    public function consumerMasterlistExport(Request $request, string $format): BinaryFileResponse|\Illuminate\Http\Response
    {
        if (! $this->canAccessReport('consumer-masterlist')) {
            abort(403);
        }

        $status = $request->get('status');
        $blockId = $request->get('block_id');
        $filename = 'consumer-masterlist-'.now()->format('Y-m-d');

        if ($format === 'pdf') {
            $query = Consumer::with(['user', 'block']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($blockId) {
                $query->where('block_id', $blockId);
            }

            $consumers = $query->orderBy('id_no')->get();

            $pdf = Pdf::loadView('reports.exports.consumer-masterlist-pdf', [
                'consumers' => $consumers,
            ]);

            return $pdf->download($filename.'.pdf');
        }

        return Excel::download(
            new ConsumerMasterlistExport($status, $blockId),
            $filename.'.xlsx'
        );
    }
}
