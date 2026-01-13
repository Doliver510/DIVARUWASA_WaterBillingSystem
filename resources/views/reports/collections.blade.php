<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Reports</div>
                <h2 class="page-title">Collections Report</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('reports.collections.export', ['format' => 'pdf', 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 12l1 5l1.5 -3l1.5 3l1 -5" /></svg>
                            Export as PDF
                        </a>
                        <a class="dropdown-item" href="{{ route('reports.collections.export', ['format' => 'excel', 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                            Export as Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row row-deck mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Total Collections</div>
                    <div class="h1 mb-0 text-success">₱{{ number_format($totalAmount, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Total Transactions</div>
                    <div class="h1 mb-0">{{ number_format($totalCount) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Date Range</div>
                    <div class="h3 mb-0">{{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Average per Transaction</div>
                    <div class="h1 mb-0">₱{{ $totalCount > 0 ? number_format($totalAmount / $totalCount, 2) : '0.00' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        {{-- By Processor Summary --}}
        @if($byProcessor->count() > 0)
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">By Cashier/Staff</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byProcessor as $summary)
                                    <tr>
                                        <td>{{ $summary['processor']->full_name }}</td>
                                        <td class="text-center">{{ $summary['count'] }}</td>
                                        <td class="text-end fw-bold">₱{{ number_format($summary['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Detailed Payments Table --}}
        <div class="col-lg-{{ $byProcessor->count() > 0 ? '8' : '12' }}">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Details</h3>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>OR Number</th>
                                <th>Date & Time</th>
                                <th>Consumer</th>
                                <th>Bill Period</th>
                                <th class="text-end">Amount</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td class="text-primary fw-bold">{{ $payment->or_number }}</td>
                                    <td class="text-muted">{{ $payment->paid_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <div>{{ $payment->consumer->full_name }}</div>
                                        <small class="text-muted">{{ $payment->consumer->id_no }}</small>
                                    </td>
                                    <td>{{ $payment->bill->billing_period_label }}</td>
                                    <td class="text-end fw-bold text-success">₱{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->processedBy->full_name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No payments found for this date range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

