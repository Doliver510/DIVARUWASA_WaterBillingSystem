<x-app-layout>
    <x-slot name="header">
        <div class="page-pretitle">Billing</div>
        <h2 class="page-title">All Bills</h2>
    </x-slot>

    <div class="row row-cards">
        {{-- Filters --}}
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Billing Period</label>
                            <select name="period" class="form-select">
                                <option value="">All Periods</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period }}" {{ $currentPeriod === $period ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ $currentStatus === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                Filter
                            </button>
                            <a href="{{ route('bills.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bills Table --}}
        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>ID No.</th>
                                <th>Consumer</th>
                                <th>Period</th>
                                <th>Consumption</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $bill)
                                <tr>
                                    <td class="text-muted">{{ $bill->consumer->id_no }}</td>
                                    <td>
                                        <div>{{ $bill->consumer->full_name }}</div>
                                    </td>
                                    <td>{{ $bill->billing_period_label }}</td>
                                    <td>{{ number_format($bill->consumption) }} cu.m</td>
                                    <td class="text-end">₱{{ number_format($bill->total_amount, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($bill->balance, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $bill->status_color }}">{{ $bill->status_label }}</span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm dropdown-toggle align-text-top" data-bs-toggle="dropdown">Actions</button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('bills.show', $bill) }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                                    View
                                                </a>
                                                <a class="dropdown-item" href="{{ route('bills.print', $bill) }}" target="_blank">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                                    Print
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No bills found.
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
