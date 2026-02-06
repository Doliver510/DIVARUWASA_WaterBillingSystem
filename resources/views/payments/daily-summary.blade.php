<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col-auto">
                <a href="{{ route('payments.index') }}" class="btn btn-ghost-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    Back
                </a>
            </div>
            <div class="col">
                <div class="page-pretitle">Cashiering</div>
                <h2 class="page-title">Daily Collection Summary</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <button onclick="window.print()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                    Print Summary
                </button>
            </div>
        </div>
    </x-slot>

    {{-- Date Selector --}}
    <div class="row mb-4 d-print-none">
        <div class="col-md-4">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ $date }}">
                <button type="submit" class="btn btn-primary">View</button>
            </form>
        </div>
    </div>

    {{-- Summary Header --}}
    <div class="row row-deck mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Date</div>
                    </div>
                    <div class="h1 mb-0">{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Transactions</div>
                    </div>
                    <div class="h1 mb-0">{{ $totalCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader text-white-50">Total Collection</div>
                    </div>
                    <div class="h1 mb-0">₱{{ number_format($totalAmount, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary by Processor --}}
    @if($byProcessor->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Collection by Cashier/Staff</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th class="text-center">Transactions</th>
                            <th class="text-end">Total Collected</th>
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
                    <tfoot>
                        <tr class="bg-light">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-center"><strong>{{ $totalCount }}</strong></td>
                            <td class="text-end"><strong>₱{{ number_format($totalAmount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- Detailed List --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payment Details</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt No.</th>
                        <th>Time</th>
                        <th>Consumer</th>
                        <th>Bill Period</th>
                        <th class="text-end">Amount</th>
                        <th>Cashier</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $index => $payment)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td class="text-primary fw-bold">{{ $payment->receipt_number }}</td>
                            <td class="text-muted">{{ $payment->paid_at->format('h:i A') }}</td>
                            <td>
                                <div>{{ $payment->consumer->full_name }}</div>
                                <small class="text-muted">{{ $payment->consumer->id_no }}</small>
                            </td>
                            <td>{{ $payment->bill->billing_period_label }}</td>
                            <td class="text-end fw-bold">₱{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->processedBy->full_name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No payments recorded for this date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($payments->count() > 0)
                    <tfoot>
                        <tr class="bg-primary text-white">
                            <td colspan="5"><strong>GRAND TOTAL</strong></td>
                            <td class="text-end"><strong>₱{{ number_format($totalAmount, 2) }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Print Footer --}}
    <div class="d-none d-print-block mt-4">
        <div class="row">
            <div class="col-6">
                <p class="mb-0">Prepared by: _______________________</p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-0">Verified by: _______________________</p>
            </div>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted">Printed: {{ now()->format('M d, Y h:i A') }}</small>
        </div>
    </div>
</x-app-layout>

