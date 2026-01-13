<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Dashboard') }}
        </h2>
        <div class="page-pretitle">Welcome back, {{ $user->first_name }}!</div>
    </x-slot>

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
