<x-app-layout>
    <x-slot name="header">
        <div class="page-pretitle">My Account</div>
        <h2 class="page-title">My Bills</h2>
    </x-slot>

    <div class="row row-cards">
        {{-- Current Bill Summary --}}
        @php
            $currentBill = $bills->first();
        @endphp
        @if($currentBill)
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Current Bill - {{ $currentBill->billing_period_label }}</h3>
                        <div class="card-actions">
                            <a href="{{ route('bills.print', $currentBill) }}" target="_blank" class="btn btn-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                Print Bill
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="datagrid">
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Billing Period</div>
                                        <div class="datagrid-content">{{ $currentBill->period_from->format('M d') }} - {{ $currentBill->period_to->format('M d, Y') }}</div>
                                    </div>
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Consumption</div>
                                        <div class="datagrid-content">{{ number_format($currentBill->consumption) }} cu.m</div>
                                    </div>
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Pay Before (No Penalty)</div>
                                        <div class="datagrid-content">{{ $currentBill->disconnection_date->format('M d, Y') }}</div>
                                    </div>
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Grace Period (With Penalty)</div>
                                        <div class="datagrid-content">{{ $currentBill->due_date_range }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-center d-flex flex-column justify-content-center">
                                <div class="mb-2">
                                    <span class="badge bg-{{ $currentBill->status_color }} fs-5 px-3 py-2">{{ $currentBill->status_label }}</span>
                                </div>
                                <div class="display-6 fw-bold text-primary mb-2">₱{{ number_format($currentBill->balance, 2) }}</div>
                                <div class="text-muted">Amount Due</div>
                                @if($currentBill->amount_paid > 0)
                                    <div class="text-success mt-1">
                                        <small>Paid: ₱{{ number_format($currentBill->amount_paid, 2) }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body text-center py-4">
                        <div class="text-muted">No current bill available.</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Billing History --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Billing History</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Consumption</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $bill)
                                <tr>
                                    <td>{{ $bill->billing_period_label }}</td>
                                    <td>{{ number_format($bill->consumption) }} cu.m</td>
                                    <td class="text-end">₱{{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($bill->amount_paid, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($bill->balance, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $bill->status_color }}">{{ $bill->status_label }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('bills.show', $bill) }}" class="btn btn-sm" title="View Details">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                            </a>
                                            <a href="{{ route('bills.print', $bill) }}" target="_blank" class="btn btn-sm" title="Print">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No billing history available.
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
