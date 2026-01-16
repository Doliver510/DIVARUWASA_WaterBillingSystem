<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Materials</div>
                <h2 class="page-title">Stock Movement History</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    Back to Materials
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
                        <div class="subheader">Total Stock In</div>
                    </div>
                    <div class="h1 mb-0 text-success">+{{ number_format($totalIn) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Stock Out</div>
                    </div>
                    <div class="h1 mb-0 text-danger">-{{ number_format($totalOut) }}</div>
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
                            <label class="form-label">Material</label>
                            <select name="material_id" class="form-select">
                                <option value="">All Materials</option>
                                @foreach($materials as $material)
                                    <option value="{{ $material->id }}" {{ request('material_id') == $material->id ? 'selected' : '' }}>
                                        {{ $material->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Stock In</option>
                                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Stock Out</option>
                                <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                Filter
                            </button>
                            <a href="{{ route('materials.stock-movements') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Movements Table --}}
        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Material</th>
                                <th>Type</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Stock Before</th>
                                <th class="text-end">Stock After</th>
                                <th>Reference</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td class="text-muted">
                                        {{ $movement->created_at->format('M d, Y') }}<br>
                                        <small>{{ $movement->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $movement->material->name }}</div>
                                        <small class="text-muted">{{ $movement->material->unit }}</small>
                                    </td>
                                    <td>
                                        @if($movement->type === 'in')
                                            <span class="badge bg-success">Stock In</span>
                                        @elseif($movement->type === 'out')
                                            <span class="badge bg-danger">Stock Out</span>
                                        @else
                                            <span class="badge bg-warning">Adjustment</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($movement->quantity > 0)
                                            <span class="text-success fw-bold">+{{ $movement->quantity }}</span>
                                        @else
                                            <span class="text-danger fw-bold">{{ $movement->quantity }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">{{ $movement->stock_before }}</td>
                                    <td class="text-end fw-bold">{{ $movement->stock_after }}</td>
                                    <td>
                                        @if($movement->reference_type === 'maintenance_request')
                                            <a href="{{ route('maintenance-requests.show', $movement->reference_id) }}" class="text-reset">
                                                Maintenance #{{ $movement->reference_id }}
                                            </a>
                                        @elseif($movement->reference_type === 'initial_stock')
                                            <span class="text-muted">Initial Stock</span>
                                        @elseif($movement->reference_type === 'stock_in')
                                            <span class="text-muted">Stock Replenishment</span>
                                        @elseif($movement->reference_type === 'restore')
                                            <span class="text-muted">Restored (Cancelled)</span>
                                        @else
                                            <span class="text-muted">{{ $movement->reference_type ?? '-' }}</span>
                                        @endif
                                        @if($movement->remarks)
                                            <br><small class="text-muted">{{ Str::limit($movement->remarks, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($movement->user)
                                            {{ $movement->user->full_name }}
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No stock movements found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($movements->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $movements->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

