<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $consumer->id_no }} — {{ $consumer->full_name }}</div>
                <h2 class="page-title">Account Ledger</h2>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-primary d-none d-md-inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                    Print
                </button>
                <a href="{{ auth()->user()->role->slug === 'consumer' ? route('bills.index') : route('consumers.show', $consumer) }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Financial Summary Cards --}}
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar rounded bg-{{ $currentBalance > 0 ? 'danger' : 'success' }}-lt">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-{{ $currentBalance > 0 ? 'danger' : 'success' }}" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2" /><path d="M12 3v3m0 12v3" /></svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-secondary small fw-medium">Current Balance</div>
                            <div class="h2 mb-0 text-{{ $currentBalance > 0 ? 'danger' : 'success' }}">
                                ₱{{ number_format($currentBalance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar rounded bg-danger-lt">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-10 10" /><path d="M8 7l9 0l0 9" /></svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-secondary small fw-medium">Total Debits ({{ $year }})</div>
                            <div class="h2 mb-0 text-danger">₱{{ number_format($totalDebits, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar rounded bg-success-lt">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17l10 -10" /><path d="M16 17l0 -9l-9 0" /></svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-secondary small fw-medium">Total Credits ({{ $year }})</div>
                            <div class="h2 mb-0 text-success">₱{{ number_format($totalCredits, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction History Table --}}
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19l16 0" /><path d="M4 15l4 -6l4 2l4 -5l4 4" /></svg>
                        Transaction History
                    </h3>
                    <div class="card-actions d-flex align-items-center gap-2">
                        <span class="text-secondary small">
                            {{ count($ledgerEntries) }} {{ Str::plural('transaction', count($ledgerEntries)) }}
                        </span>
                        <form method="GET" class="d-flex align-items-center gap-2">
                            <select name="year" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 110px;">Type</th>
                                <th>Description</th>
                                <th style="width: 120px;">Reference</th>
                                <th class="text-end" style="width: 110px;">Debit</th>
                                <th class="text-end" style="width: 110px;">Credit</th>
                                <th class="text-end" style="width: 120px;">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerEntries as $entry)
                                <tr>
                                    <td class="text-secondary">
                                        {{ \Carbon\Carbon::parse($entry['date'])->format('M d, Y') }}
                                    </td>
                                    <td>
                                        @switch($entry['type'])
                                            @case('BILL')
                                                <span class="badge bg-blue-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                                                    BILL
                                                </span>
                                                @break
                                            @case('PAYMENT')
                                                <span class="badge bg-green-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                                    PAYMENT
                                                </span>
                                                @break
                                            @case('MAINTENANCE')
                                                <span class="badge bg-orange-lt">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" /></svg>
                                                    MAINT
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $entry['description'] }}</td>
                                    <td>
                                        <a href="{{ route($entry['reference_route'], $entry['reference_id']) }}" class="text-primary text-decoration-none">
                                            {{ $entry['reference'] }}
                                        </a>
                                    </td>
                                    <td class="text-end {{ $entry['debit'] > 0 ? 'text-danger fw-medium' : '' }}">
                                        {{ $entry['debit'] > 0 ? '₱' . number_format($entry['debit'], 2) : '' }}
                                    </td>
                                    <td class="text-end {{ $entry['credit'] > 0 ? 'text-success fw-medium' : '' }}">
                                        {{ $entry['credit'] > 0 ? '₱' . number_format($entry['credit'], 2) : '' }}
                                    </td>
                                    <td class="text-end fw-bold {{ $entry['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                        ₱{{ number_format($entry['balance'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                        <p class="mb-0">No transactions found for {{ $year }}.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($ledgerEntries) > 0)
                            <tfoot>
                                <tr class="fw-bold bg-light">
                                    <td colspan="4" class="text-end text-secondary">Totals for {{ $year }}:</td>
                                    <td class="text-end text-danger">₱{{ number_format($totalDebits, 2) }}</td>
                                    <td class="text-end text-success">₱{{ number_format($totalCredits, 2) }}</td>
                                    <td class="text-end {{ $currentBalance > 0 ? 'text-danger' : 'text-success' }}">
                                        ₱{{ number_format($currentBalance, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Legend --}}
            <div class="card mt-3">
                <div class="card-body py-2">
                    <div class="d-flex gap-4 justify-content-center flex-wrap small text-secondary">
                        <div><span class="badge bg-blue-lt me-1">BILL</span> Water charges added to account</div>
                        <div><span class="badge bg-green-lt me-1">PAYMENT</span> Payments received</div>
                        <div><span class="badge bg-orange-lt me-1">MAINT</span> Material charges billed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
