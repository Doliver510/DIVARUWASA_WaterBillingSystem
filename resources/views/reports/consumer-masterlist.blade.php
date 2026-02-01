<x-app-layout>
    <x-slot name="header">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Reports</div>
                <h2 class="page-title">Consumer Masterlist</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('reports.consumer-masterlist.export', ['format' => 'pdf', 'status' => $currentStatus, 'block_id' => $currentBlock]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            Export as PDF
                        </a>
                        <a class="dropdown-item" href="{{ route('reports.consumer-masterlist.export', ['format' => 'excel', 'status' => $currentStatus, 'block_id' => $currentBlock]) }}">
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
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ $currentStatus === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Disconnected" {{ $currentStatus === 'Disconnected' ? 'selected' : '' }}>Disconnected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Block</label>
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
                    <a href="{{ route('reports.consumer-masterlist') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row row-deck mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Total Consumers</div>
                    <div class="h1 mb-0">{{ $consumers->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="subheader">Active</div>
                    <div class="h1 mb-0 text-success">{{ $totalActive }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="subheader">Disconnected</div>
                    <div class="h1 mb-0 text-danger">{{ $totalDisconnected }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">Blocks</div>
                    <div class="h1 mb-0">{{ $blocks->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Consumers Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Consumer Registry</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>ID No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consumers as $consumer)
                        <tr>
                            <td class="text-muted">{{ $consumer->id_no }}</td>
                            <td>
                                <a href="{{ route('consumers.show', $consumer) }}">
                                    {{ $consumer->user->last_name }}, {{ $consumer->user->first_name }} {{ $consumer->user->middle_name ?? '' }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $consumer->user->email ?? '-' }}</td>
                            <td>{{ $consumer->address }}</td>
                            <td>
                                <span class="badge {{ $consumer->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $consumer->status }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $consumer->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No consumers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

