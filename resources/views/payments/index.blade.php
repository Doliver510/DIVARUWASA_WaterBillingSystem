<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Cashiering</div>
                <h2 class="page-title">Payments</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('payments.daily-summary') }}" class="btn btn-outline-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M9 17v-5" /><path d="M12 17v-1" /><path d="M15 17v-3" /></svg>
                    Daily Summary
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Summary Cards --}}
    <div class="row row-deck mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Today's Collections</div>
                    </div>
                    <div class="h1 mb-0 text-success">₱{{ number_format($todayTotal, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Filtered Total</div>
                    </div>
                    <div class="h1 mb-0">₱{{ number_format($filteredTotal, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        {{-- Filters --}}
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ $currentDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Processed By</label>
                            <select name="processed_by" class="form-select">
                                <option value="">All Staff</option>
                                @foreach($processors as $processor)
                                    <option value="{{ $processor->id }}" {{ $currentProcessedBy == $processor->id ? 'selected' : '' }}>
                                        {{ $processor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search OR# / Consumer</label>
                            <input type="text" name="search" class="form-control" placeholder="OR Number or Name..." value="{{ $currentSearch }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                Filter
                            </button>
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Payments Table --}}
        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>OR Number</th>
                                <th>Date & Time</th>
                                <th>Consumer</th>
                                <th>Payment For</th>
                                <th class="text-end">Amount</th>
                                <th>Processed By</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>
                                        <span class="text-primary fw-bold">{{ $payment->or_number }}</span>
                                    </td>
                                    <td class="text-muted">
                                        {{ $payment->paid_at->format('M d, Y') }}<br>
                                        <small>{{ $payment->paid_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $payment->consumer->full_name }}</div>
                                        <small class="text-muted">ID: {{ $payment->consumer->id_no }}</small>
                                    </td>
                                    <td>
                                        @if($payment->isBillPayment() && $payment->bill)
                                            <span class="badge bg-blue-lt">Bill</span>
                                            {{ $payment->bill->billing_period_label }}
                                        @elseif($payment->isMaintenancePayment())
                                            <span class="badge bg-orange-lt">Maintenance</span>
                                            Request #{{ $payment->maintenance_request_id }}
                                        @else
                                            <span class="badge bg-secondary-lt">Payment</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success">₱{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->processedBy->full_name }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">Actions</button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('payments.show', $payment) }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                                    View Details
                                                </a>
                                                <a class="dropdown-item" href="{{ route('payments.receipt', $payment) }}" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                                    Print Receipt
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No payments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($payments->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $payments->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

