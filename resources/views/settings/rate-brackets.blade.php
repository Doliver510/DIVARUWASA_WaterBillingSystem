<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Excess Consumption Rates') }}
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

            <!-- Minimum Charge Info Card -->
            <div class="card mb-3 bg-azure-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-azure me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-droplet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7.502 19.423c2.602 2.105 6.395 2.105 8.996 0c2.602 -2.105 3.262 -5.708 1.566 -8.546l-4.89 -7.26c-.42 -.625 -1.287 -.803 -1.936 -.397a1.376 1.376 0 0 0 -.41 .397l-4.893 7.26c-1.695 2.838 -1.035 6.441 1.567 8.546z" /></svg>
                        </span>
                        <div>
                            <div class="font-weight-medium">{{ __('Minimum Charge: ₱') }}{{ number_format(\App\Models\AppSetting::getValue('base_charge', 150), 2) }}</div>
                            <div class="text-muted">
                                {{ __('Covers the first') }} {{ \App\Models\AppSetting::getValue('base_charge_covers_cubic', 10) }} {{ __('cubic meters of consumption. The rates below apply to usage beyond this.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How it Works Info Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-blue-lt me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                        </span>
                        <div>
                            <div class="font-weight-medium">{{ __('How Billing Works') }}</div>
                            <div class="text-muted">
                                {{ __('Example: For 25 cubic meters consumption → ₱150 (base) + 10 cubic meters × ₱15 (11-20 range) + 5 cubic meters × ₱20 (21-30 range) = ₱400 total') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brackets Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Rate Brackets') }}</h3>
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
                                <th>{{ __('Range (cubic meters)') }}</th>
                                <th>{{ __('Rate per cubic meter') }}</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brackets as $bracket)
                                <tr>
                                    <td>
                                        <strong>{{ $bracket->min_cubic }} - {{ $bracket->max_cubic ?? '∞' }}</strong> cubic meters
                                    </td>
                                    <td>
                                        <span class="badge bg-green-lt fs-5">₱{{ number_format($bracket->rate_per_cubic, 2) }}</span>
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
                                                            <label class="form-label">{{ __('Min (cubic meters)') }}</label>
                                                            <input type="number" name="min_cubic" class="form-control" value="{{ $bracket->min_cubic }}" min="1" step="1" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">{{ __('Max (cubic meters)') }}</label>
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
                                    <td colspan="3" class="text-center text-muted py-4">
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
                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                            {{ __('These rates apply to consumption beyond the base charge coverage (first ') }}{{ \App\Models\AppSetting::getValue('base_charge_covers_cubic', 10) }} cubic meters).
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">{{ __('Min (cubic meters)') }}</label>
                                <input type="number" name="min_cubic" class="form-control" min="1" step="1" required placeholder="e.g., 11">
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Max (cubic meters)') }}</label>
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
