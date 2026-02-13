<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $consumer->id_no }}</div>
                <h2 class="page-title">{{ $consumer->user->full_name }}</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('consumers.index') }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        {{-- Left Sidebar: Consumer Profile Card --}}
        <div class="col-lg-3">
            <div class="card card-stacked">
                <div class="card-body text-center">
                    <span class="avatar avatar-xl mb-3 rounded-circle" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode($consumer->user->full_name) }}&background=0077b6&color=fff&size=128)"></span>
                    <h3 class="mb-1">{{ $consumer->user->full_name }}</h3>
                    <p class="text-secondary mb-2">{{ $consumer->user->email ?? 'No email' }}</p>
                    @if($consumer->status === 'active')
                        <span class="badge bg-success-lt px-3 py-1">
                            <span class="badge-dot bg-success me-1"></span>Active
                        </span>
                    @else
                        <span class="badge bg-{{ $consumer->status_color }}-lt px-3 py-1">
                            <span class="badge-dot bg-{{ $consumer->status_color }} me-1"></span>{{ $consumer->status_label }}
                        </span>
                    @endif
                </div>

                {{-- Quick Financial Summary --}}
                <div class="card-body border-top p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-sm rounded bg-{{ $currentBalance > 0 ? 'danger' : 'success' }}-lt">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-{{ $currentBalance > 0 ? 'danger' : 'success' }}" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2" /><path d="M12 3v3m0 12v3" /></svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="text-secondary small">Current Balance</div>
                                    <div class="fw-bold text-{{ $currentBalance > 0 ? 'danger' : 'success' }}">₱{{ number_format($currentBalance, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-sm rounded bg-danger-lt">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-10 10" /><path d="M8 7l9 0l0 9" /></svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="text-secondary small">Total Debits ({{ $year }})</div>
                                    <div class="fw-bold text-danger">₱{{ number_format($totalDebits, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-sm rounded bg-success-lt">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17l10 -10" /><path d="M16 17l0 -9l-9 0" /></svg>
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="text-secondary small">Total Credits ({{ $year }})</div>
                                    <div class="fw-bold text-success">₱{{ number_format($totalCredits, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Account Details --}}
                <div class="card-body border-top">
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-secondary me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7l6 -3l6 3l6 -3v13l-6 3l-6 -3l-6 3v-13" /><path d="M9 4v13" /><path d="M15 7v13" /></svg>
                        <span class="text-secondary small">Block:</span>
                        <span class="fw-medium">{{ $consumer->block?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-secondary me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                        <span class="text-secondary small">Lot:</span>
                        <span class="fw-medium">{{ $consumer->lot_number ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-secondary me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>
                        <span class="text-secondary small">Address:</span>
                        <span class="fw-medium">{{ $consumer->address }}</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-secondary me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M16 3l0 4" /><path d="M8 3l0 4" /><path d="M4 11l16 0" /><path d="M11 15l1 0" /><path d="M12 15l0 3" /></svg>
                        <span class="text-secondary small">Since:</span>
                        <span class="fw-medium">{{ $consumer->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Content: Tabbed Interface --}}
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                        <li class="nav-item">
                            <a href="#tab-ledger" class="nav-link active" data-bs-toggle="tab">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19l16 0" /><path d="M4 15l4 -6l4 2l4 -5l4 4" /></svg>
                                {{ __('Account Ledger') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-info" class="nav-link" data-bs-toggle="tab">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                                {{ __('Account Info') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        {{-- Tab: Account Ledger --}}
                        <div class="tab-pane active show" id="tab-ledger">
                            {{-- Year filter bar --}}
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-light">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-secondary small fw-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M16 3l0 4" /><path d="M8 3l0 4" /><path d="M4 11l16 0" /></svg>
                                        {{ count($ledgerEntries) }} {{ Str::plural('transaction', count($ledgerEntries)) }}
                                    </span>
                                </div>
                                <form method="GET" class="d-flex align-items-center gap-2">
                                    <label class="text-secondary small fw-medium mb-0">Year:</label>
                                    <select name="year" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                        @foreach($availableYears as $y)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            {{-- Ledger table --}}
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table table-hover" style="margin-bottom: 0;">
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

                            {{-- Legend Footer --}}
                            <div class="px-3 py-2 border-top bg-light">
                                <div class="d-flex gap-4 justify-content-center flex-wrap small text-secondary">
                                    <div><span class="badge bg-blue-lt me-1">BILL</span> Water charges</div>
                                    <div><span class="badge bg-green-lt me-1">PAYMENT</span> Payments received</div>
                                    <div><span class="badge bg-orange-lt me-1">MAINT</span> Material charges</div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab: Account Info --}}
                        <div class="tab-pane" id="tab-info">
                            <div class="p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="subheader text-azure mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                            {{ __('Personal Information') }}
                                        </h4>
                                        <dl class="row mb-0">
                                            <dt class="col-5 text-secondary">{{ __('ID Number') }}:</dt>
                                            <dd class="col-7"><strong class="text-azure">{{ $consumer->id_no }}</strong></dd>

                                            <dt class="col-5 text-secondary">{{ __('First Name') }}:</dt>
                                            <dd class="col-7">{{ $consumer->user->first_name }}</dd>

                                            <dt class="col-5 text-secondary">{{ __('Middle Name') }}:</dt>
                                            <dd class="col-7">{{ $consumer->user->middle_name ?? '-' }}</dd>

                                            <dt class="col-5 text-secondary">{{ __('Last Name') }}:</dt>
                                            <dd class="col-7">{{ $consumer->user->last_name }}</dd>

                                            <dt class="col-5 text-secondary">{{ __('Email') }}:</dt>
                                            <dd class="col-7">{{ $consumer->user->email ?? 'Not set' }}</dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="subheader text-azure mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                                            {{ __('Account Details') }}
                                        </h4>
                                        <dl class="row mb-0">
                                            <dt class="col-5 text-secondary">{{ __('Block') }}:</dt>
                                            <dd class="col-7">{{ $consumer->block?->name ?? 'N/A' }}</dd>

                                            <dt class="col-5 text-secondary">{{ __('Lot Number') }}:</dt>
                                            <dd class="col-7">{{ $consumer->lot_number ?? 'N/A' }}</dd>

                                            <dt class="col-5 text-secondary">{{ __('Full Address') }}:</dt>
                                            <dd class="col-7">{{ $consumer->address }}</dd>

                                            <dt class="col-5 text-secondary">{{ __('Status') }}:</dt>
                                            <dd class="col-7">
                                                <span class="badge bg-{{ $consumer->status_color }}-lt">
                                                    <span class="badge-dot bg-{{ $consumer->status_color }} me-1"></span>{{ __($consumer->status_label) }}
                                                </span>
                                            </dd>

                                            <dt class="col-5 text-secondary">{{ __('Registered') }}:</dt>
                                            <dd class="col-7">{{ $consumer->created_at->format('M d, Y h:i A') }}</dd>

                                            <dt class="col-5 text-secondary">{{ __('Last Updated') }}:</dt>
                                            <dd class="col-7">{{ $consumer->updated_at->format('M d, Y h:i A') }}</dd>
                                        </dl>
                                    </div>
                                </div>

                                <hr class="my-3">

                                {{-- Meter Information --}}
                                <div class="row">
                                    <div class="col-12">
                                        <h4 class="subheader text-azure mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3v4" /><path d="M12 21v-2" /><path d="M3 12h4" /><path d="M17 12h4" /><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /></svg>
                                            {{ __('Meter Information') }}
                                        </h4>
                                        
                                        @if($consumer->activeMeter)
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-2"><strong>Current Meter No:</strong> <span class="text-azure">{{ $consumer->meter_number }}</span></div>
                                                            <div class="mb-2"><strong>Installed Date:</strong> {{ \Carbon\Carbon::parse($consumer->activeMeter->installed_at)->format('M d, Y') }}</div>
                                                            <div class="mb-2"><strong>Cost:</strong> ₱{{ number_format($consumer->activeMeter->meter_cost, 2) }}</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-2"><strong>Payment Status:</strong> 
                                                                @if($consumer->activeMeter->fully_paid)
                                                                    <span class="badge bg-success-lt">Fully Paid</span>
                                                                @else
                                                                    <span class="badge bg-orange-lt">Paying Installments</span>
                                                                @endif
                                                            </div>
                                                            @if(!$consumer->activeMeter->fully_paid)
                                                                <div class="mb-2"><strong>Remaining Balance:</strong> ₱{{ number_format($consumer->activeMeter->remaining_balance, 2) }}</div>
                                                                <div class="mb-2"><strong>Installments:</strong> {{ $consumer->activeMeter->installments_billed }} / {{ $consumer->activeMeter->installment_months }} billed</div>
                                                                
                                                                <form action="{{ route('meters.pay-balance', $consumer->activeMeter->id) }}" method="POST" class="mt-3">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Process full payment of ₱{{ number_format($consumer->activeMeter->remaining_balance, 2) }} for this meter?')">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                                                                        Pay Remaining Balance
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info">No active meter record found.</div>
                                        @endif

                                        <h5 class="subheader mt-4">Meter History</h5>
                                        <div class="table-responsive">
                                            <table class="table table-vcenter card-table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Meter No.</th>
                                                        <th>Installed</th>
                                                        <th>Removed</th>
                                                        <th>Reason</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($consumer->meters()->orderBy('installed_at', 'desc')->get() as $meter)
                                                        <tr>
                                                            <td>{{ $meter->meter_number }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($meter->installed_at)->format('M d, Y') }}</td>
                                                            <td>{{ $meter->removed_at ? \Carbon\Carbon::parse($meter->removed_at)->format('M d, Y') : '-' }}</td>
                                                            <td>{{ $meter->removal_reason ?? '-' }}</td>
                                                            <td>
                                                                @if($meter->removed_at)
                                                                    <span class="badge bg-secondary-lt">Archived</span>
                                                                @elseif($meter->fully_paid)
                                                                    <span class="badge bg-success-lt">Active (Paid)</span>
                                                                @else
                                                                    <span class="badge bg-orange-lt">Active (Paying)</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">No meter history available.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
