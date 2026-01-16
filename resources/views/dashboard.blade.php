<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Dashboard') }}
        </h2>
        <div class="page-pretitle">Welcome back, {{ $user->first_name }}!</div>
    </x-slot>

    {{-- Announcements Section (shown to all users) --}}
    @if(isset($announcements) && $announcements->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            @foreach($announcements as $announcement)
                <div class="alert alert-{{ $announcement->type === 'urgent' ? 'danger' : ($announcement->type === 'warning' ? 'warning' : 'info') }} alert-dismissible mb-2" role="alert">
                    <div class="d-flex">
                        <div>
                            @if($announcement->type === 'urgent')
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
                            @elseif($announcement->type === 'warning')
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="alert-title">{{ $announcement->title }}</h4>
                            <div class="text-secondary">{{ Str::limit($announcement->content, 200) }}</div>
                            <small class="text-muted">Posted {{ $announcement->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Admin Dashboard --}}
    @if($role === 'admin')
        {{-- Top Stats Row --}}
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Consumers</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-blue">{{ $activeConsumers }} active</span>
                            </div>
                        </div>
                        <div class="h1 mb-3">{{ number_format($totalConsumers) }}</div>
                        <div class="d-flex mb-2">
                            <div>Active rate</div>
                            <div class="ms-auto">
                                <span class="text-green">{{ $totalConsumers > 0 ? number_format(($activeConsumers / $totalConsumers) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: {{ $totalConsumers > 0 ? ($activeConsumers / $totalConsumers) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Today's Collections</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-green">{{ $todayTransactions }} transactions</span>
                            </div>
                        </div>
                        <div class="h1 mb-3 text-success">₱{{ number_format($todayCollections, 2) }}</div>
                        <a href="{{ route('payments.daily-summary') }}" class="btn btn-sm btn-outline-success w-100">View Details</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Month's Outstanding</div>
                        </div>
                        <div class="h1 mb-3 text-warning">₱{{ number_format($totalOutstanding, 2) }}</div>
                        <div class="d-flex mb-2">
                            <div>Collection rate</div>
                            <div class="ms-auto">
                                <span class="text-green">{{ $totalBilled > 0 ? number_format(($totalCollected / $totalBilled) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: {{ $totalBilled > 0 ? ($totalCollected / $totalBilled) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader text-white-50">Total Arrears</div>
                            <div class="ms-auto lh-1">
                                <span class="badge bg-white text-danger">{{ $consumersWithArrears }} consumers</span>
                            </div>
                        </div>
                        <div class="h1 mb-3">₱{{ number_format($totalArrears, 2) }}</div>
                        <a href="{{ route('reports.arrears') }}" class="btn btn-sm btn-light w-100">View Report</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bill Status & Maintenance Row --}}
        <div class="row row-deck row-cards mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bill Status - {{ \Carbon\Carbon::createFromFormat('Y-m', $currentPeriod)->format('F Y') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <div class="h1 text-success mb-0">{{ $billStatusCounts['paid'] }}</div>
                                <div class="text-muted small">Paid</div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="h1 text-warning mb-0">{{ $billStatusCounts['partial'] }}</div>
                                <div class="text-muted small">Partial</div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="h1 text-secondary mb-0">{{ $billStatusCounts['unpaid'] }}</div>
                                <div class="text-muted small">Unpaid</div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="h1 text-danger mb-0">{{ $billStatusCounts['overdue'] }}</div>
                                <div class="text-muted small">Overdue</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('reports.billing-summary', ['period' => $currentPeriod]) }}" class="btn btn-primary">View Billing Report</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Payments</h3>
                        <div class="card-actions">
                            <a href="{{ route('payments.index') }}" class="btn btn-sm">View All</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <tbody>
                                @forelse($recentPayments as $payment)
                                    <tr>
                                        <td class="w-1 pe-0">
                                            <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode($payment->consumer->full_name) }}&background=0077b6&color=fff&size=32)"></span>
                                        </td>
                                        <td>
                                            <div>{{ $payment->consumer->full_name }}</div>
                                            <div class="text-muted small">{{ $payment->or_number }}</div>
                                        </td>
                                        <td class="text-end text-success fw-bold">₱{{ number_format($payment->amount, 2) }}</td>
                                        <td class="text-end text-muted small">{{ $payment->paid_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No recent payments</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Consumption & Collection Stats --}}
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">This Month's Consumption</div>
                        </div>
                        <div class="h1 mb-0">{{ number_format($thisMonthConsumption) }} <small class="text-muted">cubic meters</small></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">This Year's Consumption</div>
                        </div>
                        <div class="h1 mb-0">{{ number_format($thisYearConsumption) }} <small class="text-muted">cubic meters</small></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">This Year's Total Sales</div>
                        </div>
                        <div class="h1 mb-0 text-success">₱{{ number_format($salesBreakdown['total'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Pending Maintenance</div>
                        </div>
                        <div class="h1 mb-0 {{ $pendingMaintenance > 0 ? 'text-warning' : 'text-success' }}">{{ $pendingMaintenance }}</div>
                        <a href="{{ route('maintenance-requests.index', ['status' => 'pending']) }}" class="small">View requests</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row row-deck row-cards mb-4">
            {{-- Monthly Consumption Chart --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Monthly Water Consumption (cubic meters)</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-consumption" style="height: 240px;"></div>
                    </div>
                </div>
            </div>

            {{-- Monthly Collections Chart --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Monthly Collections (₱)</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-collections" style="height: 240px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Breakdown Charts Row --}}
        <div class="row row-deck row-cards mb-4">
            {{-- Bill Payment Status (Amounts) --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payment Status - {{ \Carbon\Carbon::createFromFormat('Y-m', $currentPeriod)->format('F Y') }}</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-payment-status" style="height: 200px;"></div>
                        <div class="mt-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-success fw-bold">₱{{ number_format($billPaymentAmounts['paid'], 2) }}</div>
                                    <div class="text-muted small">Collected</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-warning fw-bold">₱{{ number_format($billPaymentAmounts['partial'], 2) }}</div>
                                    <div class="text-muted small">Partial</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-danger fw-bold">₱{{ number_format($billPaymentAmounts['unpaid'], 2) }}</div>
                                    <div class="text-muted small">Unpaid</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sales Breakdown by Source --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sales by Category - {{ now()->year }}</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-sales-breakdown" style="height: 200px;"></div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span><span class="badge bg-blue"></span> Water Bills</span>
                                <span class="fw-bold">₱{{ number_format($salesBreakdown['water'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span><span class="badge bg-orange"></span> Penalties</span>
                                <span class="fw-bold">₱{{ number_format($salesBreakdown['penalties'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><span class="badge bg-cyan"></span> Materials</span>
                                <span class="fw-bold">₱{{ number_format($salesBreakdown['materials'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Collection This Month by Source --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">This Month's Collection Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-collection-breakdown" style="height: 200px;"></div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span><span class="badge bg-primary"></span> Water</span>
                                <span class="fw-bold">₱{{ number_format($collectionBreakdown['water'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span><span class="badge bg-warning"></span> Penalties</span>
                                <span class="fw-bold">₱{{ number_format($collectionBreakdown['penalties'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><span class="badge bg-info"></span> Materials</span>
                                <span class="fw-bold">₱{{ number_format($collectionBreakdown['materials'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('consumers.index') }}" class="btn btn-outline-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 11h6m-3 -3v6" /></svg>
                            Add Consumer
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('meter-readings.index') }}" class="btn btn-outline-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 3m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" /><path d="M9 7h6" /><path d="M9 11h6" /><path d="M9 15h4" /></svg>
                            Meter Readings
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('bills.index') }}" class="btn btn-outline-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            View Bills
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('reports.collections') }}" class="btn btn-outline-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>
                            View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Email Actions --}}
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                    Email Notifications
                </h3>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <p class="mb-0">
                            <strong>{{ $billsNeedingReminders }}</strong> consumer(s) with unpaid or overdue bills can be sent payment reminders.
                        </p>
                        <small class="text-muted">This will send reminder emails to all consumers with bills due within 3 days or already overdue.</small>
                    </div>
                    <div class="col-md-4 text-end">
                        <form action="{{ route('dashboard.send-reminders') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" {{ $billsNeedingReminders === 0 ? 'disabled' : '' }} onclick="return confirm('Send payment reminder emails to {{ $billsNeedingReminders }} consumer(s)?')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                                Send Payment Reminders
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Consumption Chart
            var consumptionData = @json($monthlyConsumption);
            new ApexCharts(document.getElementById('chart-consumption'), {
                chart: {
                    type: 'bar',
                    height: 240,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                series: [{
                    name: 'Consumption',
                    data: consumptionData.map(item => item.consumption)
                }],
                xaxis: {
                    categories: consumptionData.map(item => item.month),
                    labels: { style: { fontSize: '11px' } }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) { return val.toLocaleString() + ' m³'; }
                    }
                },
                colors: ['#206bc4'],
                dataLabels: { enabled: false },
                plotOptions: {
                    bar: { borderRadius: 4, columnWidth: '60%' }
                },
                tooltip: {
                    y: { formatter: function(val) { return val.toLocaleString() + ' cubic meters'; } }
                }
            }).render();

            // Monthly Collections Chart
            var collectionsData = @json($monthlyCollections);
            new ApexCharts(document.getElementById('chart-collections'), {
                chart: {
                    type: 'area',
                    height: 240,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                series: [{
                    name: 'Collections',
                    data: collectionsData.map(item => item.amount)
                }],
                xaxis: {
                    categories: collectionsData.map(item => item.month),
                    labels: { style: { fontSize: '11px' } }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) { return '₱' + val.toLocaleString(); }
                    }
                },
                colors: ['#2fb344'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.5, opacityTo: 0.1 }
                },
                tooltip: {
                    y: { formatter: function(val) { return '₱' + val.toLocaleString(); } }
                }
            }).render();

            // Payment Status Donut
            var paymentAmounts = @json($billPaymentAmounts);
            new ApexCharts(document.getElementById('chart-payment-status'), {
                chart: {
                    type: 'donut',
                    height: 200,
                    fontFamily: 'inherit',
                },
                series: [paymentAmounts.paid, paymentAmounts.partial, paymentAmounts.unpaid],
                labels: ['Collected', 'Partial', 'Unpaid'],
                colors: ['#2fb344', '#f59f00', '#d63939'],
                legend: { show: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return '₱' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    y: { formatter: function(val) { return '₱' + val.toLocaleString(); } }
                }
            }).render();

            // Sales Breakdown Donut
            var salesData = @json($salesBreakdown);
            new ApexCharts(document.getElementById('chart-sales-breakdown'), {
                chart: {
                    type: 'donut',
                    height: 200,
                    fontFamily: 'inherit',
                },
                series: [salesData.water, salesData.penalties, salesData.materials],
                labels: ['Water Bills', 'Penalties', 'Materials'],
                colors: ['#206bc4', '#f76707', '#17a2b8'],
                legend: { show: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return '₱' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    y: { formatter: function(val) { return '₱' + val.toLocaleString(); } }
                }
            }).render();

            // Collection Breakdown Donut
            var collectionData = @json($collectionBreakdown);
            new ApexCharts(document.getElementById('chart-collection-breakdown'), {
                chart: {
                    type: 'donut',
                    height: 200,
                    fontFamily: 'inherit',
                },
                series: [collectionData.water, collectionData.penalties, collectionData.materials],
                labels: ['Water', 'Penalties', 'Materials'],
                colors: ['#206bc4', '#f59f00', '#17a2b8'],
                legend: { show: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'This Month',
                                    formatter: function(w) {
                                        return '₱' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    y: { formatter: function(val) { return '₱' + val.toLocaleString(); } }
                }
            }).render();
        });
        </script>
        @endpush
    @endif

    {{-- Cashier Dashboard --}}
    @if($role === 'cashier')
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="subheader text-white-50">Today's Total Collections</div>
                        <div class="h1 mb-0">₱{{ number_format($todayCollections, 2) }}</div>
                        <div class="mt-2 small">{{ $todayTransactions }} transactions</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="subheader text-white-50">My Collections Today</div>
                        <div class="h1 mb-0">₱{{ number_format($myCollections, 2) }}</div>
                        <div class="mt-2 small">{{ $myTransactions }} transactions</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <a href="{{ route('bills.index') }}" class="btn btn-lg btn-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                            Process Payment
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <a href="{{ route('payments.daily-summary') }}" class="btn btn-lg btn-outline-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>
                            Daily Summary
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Payments</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>OR #</th>
                                    <th>Consumer</th>
                                    <th class="text-end">Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                    <tr>
                                        <td class="text-primary">{{ $payment->or_number }}</td>
                                        <td>{{ $payment->consumer->full_name }}</td>
                                        <td class="text-end text-success fw-bold">₱{{ number_format($payment->amount, 2) }}</td>
                                        <td class="text-muted">{{ $payment->paid_at->format('h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No recent payments</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bills with Highest Balance</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Consumer</th>
                                    <th>Period</th>
                                    <th class="text-end">Balance</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingBills as $bill)
                                    <tr>
                                        <td>{{ $bill->consumer->full_name }}</td>
                                        <td>{{ $bill->billing_period_label }}</td>
                                        <td class="text-end text-warning fw-bold">₱{{ number_format($bill->balance, 2) }}</td>
                                        <td>
                                            <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-success">Pay</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No pending bills</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Meter Reader Dashboard --}}
    @if($role === 'meter-reader')
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Assigned Blocks</div>
                        <div class="h1 mb-0">{{ $assignedBlocks->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Assigned Consumers</div>
                        <div class="h1 mb-0">{{ $assignedConsumers }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="subheader text-white-50">Readings This Period</div>
                        <div class="h1 mb-0">{{ $readingsThisPeriod }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="subheader text-white-50">Pending Readings</div>
                        <div class="h1 mb-0">{{ $pendingReadings }}</div>
                    </div>
                </div>
            </div>
        </div>

    <div class="card">
        <div class="card-header">
                <h3 class="card-title">Quick Action</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('meter-readings.index') }}" class="btn btn-lg btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    Enter Meter Readings
                </a>
            </div>
        </div>
    @endif

    {{-- Maintenance Staff Dashboard --}}
    @if($role === 'maintenance-staff')
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="subheader text-white-50">Pending Requests</div>
                        <div class="h1 mb-0">{{ $pendingRequests }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="subheader text-white-50">In Progress</div>
                        <div class="h1 mb-0">{{ $inProgressRequests }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card bg-success text-white">
        <div class="card-body">
                        <div class="subheader text-white-50">Completed This Month</div>
                        <div class="h1 mb-0">{{ $completedThisMonth }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Maintenance Requests</h3>
                <div class="card-actions">
                    <a href="{{ route('maintenance-requests.index') }}" class="btn btn-sm">View All</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Consumer</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                            <tr>
                                <td>{{ $request->consumer->full_name }}</td>
                                <td>{{ Str::limit($request->description, 40) }}</td>
                                <td>
                                    <span class="badge bg-{{ $request->status === 'pending' ? 'warning' : ($request->status === 'in_progress' ? 'primary' : 'success') }}">
                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $request->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No maintenance requests</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Consumer Dashboard --}}
    @if($role === 'consumer')
        @if($consumer)
            <div class="row row-deck row-cards mb-4">
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="subheader">Account Number</div>
                            <div class="h1 mb-0">{{ $consumer->id_no }}</div>
                            <div class="mt-2">
                                <span class="badge {{ $consumer->status === 'Active' ? 'bg-success' : 'bg-danger' }}">{{ $consumer->status }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    @if($currentBill)
                        <div class="card {{ $currentBill->balance > 0 ? 'bg-warning' : 'bg-success' }} text-white">
                            <div class="card-body">
                                <div class="subheader text-white-50">Current Bill ({{ $currentBill->billing_period_label }})</div>
                                <div class="h1 mb-0">₱{{ number_format($currentBill->balance, 2) }}</div>
                                <div class="mt-2">
                                    <span class="badge bg-white text-dark">{{ $currentBill->status_label }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card">
                            <div class="card-body">
                                <div class="subheader">Current Bill</div>
                                <div class="h3 text-muted">No bill yet</div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card {{ $totalArrears > 0 ? 'bg-danger text-white' : '' }}">
                        <div class="card-body">
                            <div class="subheader {{ $totalArrears > 0 ? 'text-white-50' : '' }}">Total Balance Due</div>
                            <div class="h1 mb-0">₱{{ number_format($totalArrears, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cards">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Payments</h3>
                            <div class="card-actions">
                                <a href="{{ route('bills.index') }}" class="btn btn-sm">View Bills</a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <tbody>
                                    @forelse($recentPayments as $payment)
                                        <tr>
                                            <td>{{ $payment->or_number }}</td>
                                            <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                                            <td class="text-end text-success fw-bold">₱{{ number_format($payment->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No payment history</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <a href="{{ route('bills.index') }}" class="btn btn-outline-primary w-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                                        My Bills
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('maintenance-requests.create') }}" class="btn btn-outline-primary w-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" /></svg>
                                        Request Maintenance
                                    </a>
                                </div>
                            </div>
                            @if($pendingRequests > 0)
                                <div class="alert alert-info mt-3 mb-0">
                                    You have {{ $pendingRequests }} pending maintenance request(s).
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                Your consumer profile has not been set up yet. Please contact the administrator.
    </div>
        @endif
    @endif
</x-app-layout>
