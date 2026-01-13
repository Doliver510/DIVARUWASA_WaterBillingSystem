<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Reports</div>
                <h2 class="page-title">Billing Summary Report</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('reports.billing-summary.export', ['format' => 'pdf', 'period' => $period]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            Export as PDF
                        </a>
                        <a class="dropdown-item" href="{{ route('reports.billing-summary.export', ['format' => 'excel', 'period' => $period]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
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
                    <label class="form-label">Billing Period</label>
                    <select name="period" class="form-select">
                        @foreach($periods as $p)
                            <option value="{{ $p }}" {{ $period === $p ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $p)->format('F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
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
                    <div class="subheader">Total Billed</div>
                    <div class="h1 mb-0">₱{{ number_format($summary['total_billed'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Total Collected</div>
                    <div class="h1 mb-0 text-success">₱{{ number_format($summary['total_collected'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Outstanding</div>
                    <div class="h1 mb-0 text-warning">₱{{ number_format($summary['total_outstanding'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Collection Rate</div>
                    <div class="h1 mb-0">{{ $summary['total_billed'] > 0 ? number_format(($summary['total_collected'] / $summary['total_billed']) * 100, 1) : 0 }}%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Breakdown --}}
    <div class="row row-deck mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="h1 mb-0 text-success">{{ $summary['count_paid'] }}</div>
                    <div class="subheader">Paid</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="h1 mb-0 text-warning">{{ $summary['count_partial'] }}</div>
                    <div class="subheader">Partial</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <div class="h1 mb-0 text-secondary">{{ $summary['count_unpaid'] }}</div>
                    <div class="subheader">Unpaid</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <div class="h1 mb-0 text-danger">{{ $summary['count_overdue'] }}</div>
                    <div class="subheader">Overdue</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bills Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bills for {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>ID No.</th>
                        <th>Consumer</th>
                        <th class="text-center">Consumption</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        <tr>
                            <td class="text-muted">{{ $bill->consumer->id_no }}</td>
                            <td>{{ $bill->consumer->full_name }}</td>
                            <td class="text-center">{{ number_format($bill->consumption) }} cu.m</td>
                            <td class="text-end">₱{{ number_format($bill->total_amount, 2) }}</td>
                            <td class="text-end text-success">₱{{ number_format($bill->amount_paid, 2) }}</td>
                            <td class="text-end {{ $bill->balance > 0 ? 'text-warning' : '' }}">₱{{ number_format($bill->balance, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $bill->status_color }}">{{ $bill->status_label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No bills found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-light fw-bold">
                        <td colspan="3">TOTAL</td>
                        <td class="text-end">₱{{ number_format($summary['total_billed'], 2) }}</td>
                        <td class="text-end text-success">₱{{ number_format($summary['total_collected'], 2) }}</td>
                        <td class="text-end text-warning">₱{{ number_format($summary['total_outstanding'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>

