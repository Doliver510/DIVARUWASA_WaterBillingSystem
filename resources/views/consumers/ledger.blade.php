<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $consumer->id_no }} - {{ $consumer->full_name }}</div>
                <h2 class="page-title">Account Ledger</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ auth()->user()->role->slug === 'consumer' ? route('bills.index') : route('consumers.show', $consumer) }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        {{-- Consumer Summary Card --}}
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <span class="avatar avatar-lg mb-3" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode($consumer->full_name) }}&background=0077b6&color=fff&size=96)"></span>
                    <h4 class="mb-0">{{ $consumer->full_name }}</h4>
                    <p class="text-muted mb-2">{{ $consumer->id_no }}</p>
                    @if($consumer->status === 'active')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Disconnected</span>
                    @endif
                </div>
                <div class="card-body border-top">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="text-muted small">Total Debits</div>
                            <div class="h4 mb-0 text-danger">₱{{ number_format($totalDebits, 2) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Total Credits</div>
                            <div class="h4 mb-0 text-success">₱{{ number_format($totalCredits, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <div class="text-muted small">Current Balance</div>
                    <div class="h3 mb-0 {{ $currentBalance > 0 ? 'text-danger' : 'text-success' }}">
                        ₱{{ number_format($currentBalance, 2) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Ledger Table --}}
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Transaction History</h3>
                    <div class="card-actions">
                        <form method="GET" class="d-flex gap-2">
                            <select name="year" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Reference</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerEntries as $entry)
                                <tr>
                                    <td class="text-muted">
                                        {{ \Carbon\Carbon::parse($entry['date'])->format('M d, Y') }}
                                    </td>
                                    <td>
                                        @switch($entry['type'])
                                            @case('BILL')
                                                <span class="badge bg-blue-lt">BILL</span>
                                                @break
                                            @case('PAYMENT')
                                                <span class="badge bg-green-lt">PAYMENT</span>
                                                @break
                                            @case('MAINTENANCE')
                                                <span class="badge bg-orange-lt">MAINTENANCE</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $entry['description'] }}</td>
                                    <td>
                                        <a href="{{ route($entry['reference_route'], $entry['reference_id']) }}" class="text-primary">
                                            {{ $entry['reference'] }}
                                        </a>
                                    </td>
                                    <td class="text-end {{ $entry['debit'] > 0 ? 'text-danger' : '' }}">
                                        {{ $entry['debit'] > 0 ? '₱' . number_format($entry['debit'], 2) : '' }}
                                    </td>
                                    <td class="text-end {{ $entry['credit'] > 0 ? 'text-success' : '' }}">
                                        {{ $entry['credit'] > 0 ? '₱' . number_format($entry['credit'], 2) : '' }}
                                    </td>
                                    <td class="text-end fw-bold {{ $entry['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                        ₱{{ number_format($entry['balance'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                        <p class="mb-0">No transactions found for {{ $year }}.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($ledgerEntries) > 0)
                            <tfoot class="bg-light">
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">Totals for {{ $year }}:</td>
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
                    <div class="d-flex gap-4 justify-content-center small text-muted">
                        <div>
                            <span class="badge bg-blue-lt me-1">BILL</span> Water charges added to account
                        </div>
                        <div>
                            <span class="badge bg-green-lt me-1">PAYMENT</span> Payments received
                        </div>
                        <div>
                            <span class="badge bg-orange-lt me-1">MAINTENANCE</span> Material charges billed
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
