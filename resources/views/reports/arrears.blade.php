<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Reports</div>
                <h2 class="page-title">Arrears / Outstanding Report</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('reports.arrears.export', ['format' => 'pdf', 'block_id' => $currentBlock]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            Export as PDF
                        </a>
                        <a class="dropdown-item" href="{{ route('reports.arrears.export', ['format' => 'excel', 'block_id' => $currentBlock]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            Export as Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Block</label>
                    <select name="block_id" class="form-select">
                        <option value="">All Blocks</option>
                        @foreach($blocks as $block)
                            <option value="{{ $block->id }}" {{ $currentBlock == $block->id ? 'selected' : '' }}>
                                {{ $block->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('reports.arrears') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="row row-deck mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="subheader text-white-50">Total Outstanding Arrears</div>
                    <div class="h1 mb-0">₱{{ number_format($totalArrears, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Consumers with Arrears</div>
                    <div class="h1 mb-0">{{ $consumers->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Average Arrears</div>
                    <div class="h1 mb-0">₱{{ $consumers->count() > 0 ? number_format($totalArrears / $consumers->count(), 2) : '0.00' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Arrears Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Consumers with Outstanding Balance</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>ID No.</th>
                        <th>Consumer Name</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th class="text-center">Unpaid Bills</th>
                        <th class="text-end">Total Arrears</th>
                        <th>Oldest Unpaid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consumers as $consumer)
                        <tr>
                            <td class="text-muted">{{ $consumer->id_no }}</td>
                            <td>
                                <a href="{{ route('consumers.show', $consumer) }}">{{ $consumer->full_name }}</a>
                            </td>
                            <td>{{ $consumer->address }}</td>
                            <td>
                                <span class="badge {{ $consumer->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $consumer->status }}
                                </span>
                            </td>
                            <td class="text-center">{{ $consumer->bills->count() }}</td>
                            <td class="text-end fw-bold text-danger">₱{{ number_format($consumer->total_arrears, 2) }}</td>
                            <td class="text-muted">
                                @if($consumer->oldest_unpaid)
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $consumer->oldest_unpaid->billing_period)->format('M Y') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No consumers with outstanding arrears.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

