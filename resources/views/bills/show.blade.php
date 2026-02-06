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
                <div class="page-pretitle">Bill Details</div>
                <h2 class="page-title">{{ $bill->billing_period_label }}</h2>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                @if(in_array(auth()->user()->role->slug, ['admin', 'cashier']) && $bill->balance > 0)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                        Record Payment
                    </button>
                @endif
                <a href="{{ route('bills.print', $bill) }}" target="_blank" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                    Print Bill
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
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
                            <div class="datagrid-content">{{ $bill->consumer->id_no }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Name</div>
                            <div class="datagrid-content">{{ $bill->consumer->full_name }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Address</div>
                            <div class="datagrid-content">{{ $bill->consumer->address }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing Period & Status --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Billing Status</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Period</div>
                            <div class="datagrid-content">{{ $bill->period_from->format('M d') }} - {{ $bill->period_to->format('M d, Y') }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">
                                <span class="badge bg-{{ $bill->status_color }}">{{ $bill->status_label }}</span>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Disconnection Date</div>
                            <div class="datagrid-content">{{ $bill->disconnection_date->format('M d, Y') }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Grace Period</div>
                            <div class="datagrid-content">{{ $bill->due_date_range }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Meter Reading --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Meter Reading</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Previous Reading</div>
                            <div class="datagrid-content">{{ number_format($bill->previous_reading) }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Present Reading</div>
                            <div class="datagrid-content">{{ number_format($bill->present_reading) }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Consumption</div>
                            <div class="datagrid-content"><strong>{{ number_format($bill->consumption) }} cu.m</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charges Breakdown --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Charges Breakdown</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-vcenter card-table">
                        <tbody>
                            {{-- Water Charge Breakdown --}}
                            @if($chargeBreakdown['base_charge'] > 0)
                                <tr>
                                    <td>Minimum Charge (0-{{ $chargeBreakdown['base_covers'] }} cu.m)</td>
                                    <td class="text-end">₱{{ number_format($chargeBreakdown['base_charge'], 2) }}</td>
                                </tr>
                            @endif
                            @foreach($chargeBreakdown['tiers'] as $tier)
                                <tr>
                                    <td>{{ $tier['range'] }} ({{ $tier['units'] }} cu.m × ₱{{ number_format($tier['rate'], 2) }})</td>
                                    <td class="text-end">₱{{ number_format($tier['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-light">
                                <td><strong>Water Charge</strong></td>
                                <td class="text-end"><strong>₱{{ number_format($bill->water_charge, 2) }}</strong></td>
                            </tr>

                            @if($bill->arrears > 0)
                                <tr>
                                    <td>Arrears (Previous Balance)</td>
                                    <td class="text-end">₱{{ number_format($bill->arrears, 2) }}</td>
                                </tr>
                            @endif

                            @if($bill->penalty > 0)
                                <tr class="text-danger">
                                    <td>Late Payment Penalty</td>
                                    <td class="text-end">₱{{ number_format($bill->penalty, 2) }}</td>
                                </tr>
                            @endif

                            @if($bill->other_charges > 0)
                                <tr>
                                    <td>Other Charges (Materials, etc.)</td>
                                    <td class="text-end">₱{{ number_format($bill->other_charges, 2) }}</td>
                                </tr>
                            @endif

                            <tr class="table-primary">
                                <td><strong>Total Amount</strong></td>
                                <td class="text-end"><strong>₱{{ number_format($bill->total_amount, 2) }}</strong></td>
                            </tr>
                            @if($bill->amount_paid > 0)
                                <tr class="text-success">
                                    <td>Amount Paid</td>
                                    <td class="text-end">₱{{ number_format($bill->amount_paid, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="table-warning">
                                <td><strong>Balance Due</strong></td>
                                <td class="text-end"><strong>₱{{ number_format($bill->balance, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($bill->remarks)
            <div class="col-12">
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Remarks</h3>
                    </div>
                    <div class="card-body">
                        {{ $bill->remarks }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Payment History --}}
        @if($bill->payments->count() > 0)
            <div class="col-12">
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Payment History</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Receipt No.</th>
                                    <th>Date & Time</th>
                                    <th class="text-end">Amount</th>
                                    <th>Processed By</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bill->payments as $payment)
                                    <tr>
                                        <td class="text-primary fw-bold">{{ $payment->receipt_number }}</td>
                                        <td class="text-muted">{{ $payment->paid_at->format('M d, Y h:i A') }}</td>
                                        <td class="text-end text-success fw-bold">₱{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->processedBy->full_name }}</td>
                                        <td>
                                            <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                Print OR
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Payment Modal --}}
    @if(in_array(auth()->user()->role->slug, ['admin', 'cashier']) && $bill->balance > 0)
        <div class="modal modal-blur fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('payments.store', $bill) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            {{-- Bill Summary --}}
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Consumer:</span>
                                    <strong>{{ $bill->consumer->full_name }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Bill Period:</span>
                                    <strong>{{ $bill->billing_period_label }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Balance Due:</span>
                                    <strong class="text-danger">₱{{ number_format($bill->balance, 2) }}</strong>
                                </div>
                            </div>

                            {{-- Amount Field --}}
                            <div class="mb-3">
                                <label class="form-label required">Payment Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                        step="0.01" min="0.01" max="{{ $bill->balance }}"
                                        value="{{ old('amount', $bill->balance) }}" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">
                                    Maximum: ₱{{ number_format($bill->balance, 2) }}
                                </small>
                            </div>

                            {{-- Quick Amount Buttons --}}
                            <div class="mb-3">
                                <label class="form-label">Quick Amount</label>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-secondary quick-amount" data-amount="{{ $bill->balance }}">
                                        Full (₱{{ number_format($bill->balance, 2) }})
                                    </button>
                                    @if($bill->balance > 100)
                                        <button type="button" class="btn btn-outline-secondary quick-amount" data-amount="{{ floor($bill->balance / 2) }}">
                                            Half (₱{{ number_format(floor($bill->balance / 2), 2) }})
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Remarks --}}
                            <div class="mb-3">
                                <label class="form-label">Remarks (Optional)</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Any notes about this payment...">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.querySelectorAll('.quick-amount').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelector('input[name="amount"]').value = this.dataset.amount;
                });
            });
        </script>
    @endif
</x-app-layout>
