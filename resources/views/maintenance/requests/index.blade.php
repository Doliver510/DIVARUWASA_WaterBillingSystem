<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Maintenance Requests') }}
        </h2>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        </div>
                        <div>{{ session('success') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>{{ session('error') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            <!-- Filter Tabs -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted me-2">{{ __('Filter:') }}</span>
                        <a href="{{ route('maintenance-requests.index') }}" class="btn btn-sm {{ !$currentStatus ? 'btn-primary' : 'btn-ghost-primary' }}">
                            {{ __('All') }}
                        </a>
                        @foreach($statuses as $key => $label)
                            <a href="{{ route('maintenance-requests.index', ['status' => $key]) }}" class="btn btn-sm {{ $currentStatus === $key ? 'btn-primary' : 'btn-ghost-primary' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('All Requests') }}</h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-new-request">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            {{ __('New Request') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Consumer') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Material Cost') }}</th>
                                <th>{{ __('Requested') }}</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr>
                                    <td>
                                        <a href="{{ route('maintenance-requests.show', $request) }}" class="text-reset">
                                            #{{ $request->id }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $request->consumer->user->full_name ?? 'N/A' }}</strong>
                                            <span class="text-muted small">ID: {{ $request->consumer->id_no ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $request->request_type_label }}</td>
                                    <td>
                                        <span class="badge bg-{{ $request->status_color }}-lt">
                                            {{ $request->status_label }}
                                        </span>
                                    </td>
                                    <td>₱{{ number_format($request->total_material_cost, 2) }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $request->requested_at->format('M d, Y') }}</span>
                                            <span class="text-muted small">{{ $request->requested_at->format('h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('maintenance-requests.show', $request) }}" class="btn btn-ghost-primary btn-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        {{ __('No maintenance requests found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- New Request Modal -->
    <div class="modal modal-blur fade" id="modal-new-request" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('maintenance-requests.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('New Maintenance Request') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if(auth()->user()->role->slug !== 'consumer')
                            <div class="mb-3">
                                <label class="form-label required">{{ __('Consumer') }}</label>
                                <select name="consumer_id" class="form-select" required>
                                    <option value="">{{ __('Select a consumer...') }}</option>
                                    @php
                                        $consumers = \App\Models\Consumer::with('user')->whereHas('user')->get();
                                    @endphp
                                    @foreach($consumers as $consumer)
                                        <option value="{{ $consumer->id }}">
                                            {{ $consumer->user->full_name }} (ID: {{ $consumer->id_no }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Request Type') }}</label>
                            <select name="request_type" class="form-select" required>
                                <option value="pipe_leak">{{ __('Pipe Leak') }}</option>
                                <option value="meter_replacement">{{ __('Meter Replacement') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="{{ __('Describe the issue or provide additional details...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit Request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

