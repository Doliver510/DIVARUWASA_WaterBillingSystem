<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="{{ url()->previous() }}" class="btn btn-ghost-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    Back
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Payment Details</div>
                <h2 class="page-title">{{ $payment->receipt_number }}</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                    Print Receipt
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        {{-- Payment Info --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Information</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Receipt No.</div>
                            <div class="datagrid-content text-primary fw-bold">{{ $payment->receipt_number }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Date & Time</div>
                            <div class="datagrid-content">{{ $payment->paid_at->format('F d, Y - h:i A') }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Amount Paid</div>
                            <div class="datagrid-content text-success fw-bold fs-3">₱{{ number_format($payment->amount, 2) }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Payment Method</div>
                            <div class="datagrid-content">
                                <span class="badge bg-green">{{ ucfirst($payment->payment_method) }}</span>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Processed By</div>
                            <div class="datagrid-content">{{ $payment->processedBy->full_name }}</div>
                        </div>
                        @if($payment->remarks)
                            <div class="datagrid-item">
                                <div class="datagrid-title">Remarks</div>
                                <div class="datagrid-content">{{ $payment->remarks }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Consumer Info --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Consumer Information</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">ID No.</div>
                            <div class="datagrid-content">{{ $payment->consumer->id_no }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Name</div>
                            <div class="datagrid-content">{{ $payment->consumer->full_name }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Address</div>
                            <div class="datagrid-content">{{ $payment->consumer->address }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bill Info --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bill Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-2">
                                <span class="text-muted">Billing Period</span>
                                <div class="fw-bold">{{ $payment->bill->billing_period_label }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <span class="text-muted">Total Bill Amount</span>
                                <div class="fw-bold">₱{{ number_format($payment->bill->total_amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <span class="text-muted">Balance Before Payment</span>
                                <div class="fw-bold">₱{{ number_format($payment->balance_before, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <span class="text-muted">Balance After Payment</span>
                                <div class="fw-bold {{ $payment->balance_after <= 0 ? 'text-success' : 'text-warning' }}">
                                    ₱{{ number_format($payment->balance_after, 2) }}
                                    @if($payment->balance_after <= 0)
                                        <span class="badge bg-success ms-1">Fully Paid</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('bills.show', $payment->bill) }}" class="btn btn-outline-primary">
                        View Full Bill Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

