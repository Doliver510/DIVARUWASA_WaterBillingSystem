<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Water Rate Brackets') }}
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

            <!-- Info Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-blue-lt me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                        </span>
                        <div>
                            <div class="font-weight-medium">{{ __('How Tiered Billing Works') }}</div>
                            <div class="text-muted">
                                {{ __('Each bracket applies to consumption within its range. Example: For 25 cu.m, the first 10 cu.m uses Tier 1 rate, next 10 cu.m uses Tier 2, and remaining 5 cu.m uses Tier 3.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brackets Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Rate Tiers') }}</h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-bracket">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            {{ __('Add Bracket') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>{{ __('Tier') }}</th>
                                <th>{{ __('Range (cu.m)') }}</th>
                                <th>{{ __('Rate per cu.m') }}</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brackets as $index => $bracket)
                                <tr>
                                    <td>
                                        <span class="badge bg-blue-lt">Tier {{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        {{ $bracket->min_cubic }} - {{ $bracket->max_cubic ?? '∞' }}
                                    </td>
                                    <td>
                                        <strong>₱{{ number_format($bracket->rate_per_cubic, 2) }}</strong>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="#" class="btn btn-ghost-primary btn-icon" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $bracket->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                            </a>
                                            <form action="{{ route('rate-brackets.destroy', $bracket) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this bracket?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost-danger btn-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal for each bracket -->
                                <div class="modal modal-blur fade" id="modal-edit-{{ $bracket->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('rate-brackets.update', $bracket) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('Edit Rate Bracket') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
<div class="col-6">
                                                <label class="form-label">{{ __('Min Cubic (cu.m)') }}</label>
                                                <input type="number" name="min_cubic" class="form-control" value="{{ $bracket->min_cubic }}" min="0" step="1" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">{{ __('Max Cubic (cu.m)') }}</label>
                                                <input type="number" name="max_cubic" class="form-control" value="{{ $bracket->max_cubic }}" min="1" step="1" placeholder="Leave empty for unlimited">
                                            </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Rate per Cubic Meter (₱)') }}</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">₱</span>
                                                            <input type="number" step="0.01" name="rate_per_cubic" class="form-control" value="{{ $bracket->rate_per_cubic }}" min="0" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        {{ __('No rate brackets configured. Add one to get started.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Bracket Modal -->
    <div class="modal modal-blur fade" id="modal-add-bracket" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('rate-brackets.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add Rate Bracket') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">{{ __('Min Cubic (cu.m)') }}</label>
                                <input type="number" name="min_cubic" class="form-control" min="0" step="1" required placeholder="e.g., 0">
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Max Cubic (cu.m)') }}</label>
                                <input type="number" name="max_cubic" class="form-control" min="1" step="1" placeholder="Leave empty for unlimited">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Rate per Cubic Meter (₱)') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="rate_per_cubic" class="form-control" min="0" required placeholder="e.g., 15.00">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Add Bracket') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

