<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-8">
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

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                        </div>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{-- Period Lock Warning --}}
            @if(isset($currentPeriodLocked) && $currentPeriodLocked)
                <div class="alert alert-warning" role="alert">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                        </div>
                        <div>
                            <strong>Current period locked:</strong> Rates for {{ $currentPeriod ?? 'this period' }} are locked. 
                            Changes will apply to future billing periods only.
                        </div>
                    </div>
                </div>
            @endif

            <form id="settingsForm" action="{{ route('settings.update') }}" method="POST">
                @csrf

                {{-- Billing Rates Card --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                Billing Rates
                            </h3>
                            <p class="card-subtitle">Water consumption charges and minimum billing</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Minimum Charge
                                    <span class="form-label-description">Base monthly rate</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="1" name="settings[base_charge]" 
                                           class="form-control" 
                                           value="{{ $settings->firstWhere('key', 'base_charge')?->value ?? '150' }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Minimum Covers
                                    <span class="form-label-description">Cubic meters included</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="1" name="settings[base_charge_covers_cubic]" 
                                           class="form-control" 
                                           value="{{ $settings->firstWhere('key', 'base_charge_covers_cubic')?->value ?? '10' }}"
                                           required>
                                    <span class="input-group-text">cu.m</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment & Penalties Card --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                                Payment & Penalties
                            </h3>
                            <p class="card-subtitle">Late payment fees and grace period</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Penalty Fee
                                    <span class="form-label-description">Charged for late payments</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="1" name="settings[penalty_fee]" 
                                           class="form-control" 
                                           value="{{ $settings->firstWhere('key', 'penalty_fee')?->value ?? '50' }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Grace Period
                                    <span class="form-label-description">Days before penalty applies</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="1" name="settings[grace_period_days]" 
                                           class="form-control" 
                                           value="{{ $settings->firstWhere('key', 'grace_period_days')?->value ?? '5' }}"
                                           required>
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing Cycle Card --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-info" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                Billing Cycle
                            </h3>
                            <p class="card-subtitle">Billing period configuration</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Cycle Start Day
                                    <span class="form-label-description">Day of month (1-28)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
                                    </span>
                                    <input type="number" step="1" min="1" max="28" name="settings[billing_cycle_start_day]" 
                                           class="form-control" 
                                           value="{{ $settings->firstWhere('key', 'billing_cycle_start_day')?->value ?? '10' }}"
                                           required>
                                </div>
                                <small class="text-muted">
                                    Example: Day 10 means billing period runs from the 10th of one month to the 10th of the next.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save Button - Opens Confirmation Modal --}}
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confirmModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Sidebar --}}
        <div class="col-lg-4">
            <div class="card bg-primary-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="avatar bg-primary text-white me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                        </span>
                        <div>
                            <h4 class="mb-0">Settings Guide</h4>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h5 class="mb-1">Minimum Charge</h5>
                        <p class="text-muted mb-0 small">The base amount charged to all consumers, covering the first X cubic meters of water usage.</p>
                    </div>
                    <div class="mb-3">
                        <h5 class="mb-1">Penalty Fee</h5>
                        <p class="text-muted mb-0 small">Added to bills when payment is not made within the grace period after the due date.</p>
                    </div>
                    <div class="mb-3">
                        <h5 class="mb-1">Billing Cycle</h5>
                        <p class="text-muted mb-0 small">Determines the start day of each billing period. Readings taken on this day mark the end of one period and start of the next.</p>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <h5 class="mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-warning me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                            Rate Locking
                        </h5>
                        <p class="text-muted mb-0 small">Once meter readings start for a billing period, rates are locked to ensure consistent billing. Changes will apply to future periods.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div class="modal modal-blur fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status bg-warning"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-warning mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                    <h3>Confirm Settings Change</h3>
                    <div class="text-muted">
                        You are about to modify billing settings. This will affect future bill calculations.
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Type <strong>CONFIRM</strong> to proceed:</label>
                        <input type="text" id="confirmInput" class="form-control text-center" placeholder="Type CONFIRM here" autocomplete="off">
                        <div id="confirmError" class="invalid-feedback">Please type CONFIRM exactly</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">Cancel</button>
                            </div>
                            <div class="col">
                                <button type="button" id="confirmSubmitBtn" class="btn btn-warning w-100" disabled>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmInput = document.getElementById('confirmInput');
            const confirmBtn = document.getElementById('confirmSubmitBtn');
            const confirmError = document.getElementById('confirmError');
            const settingsForm = document.getElementById('settingsForm');

            confirmInput.addEventListener('input', function() {
                const isValid = this.value === 'CONFIRM';
                confirmBtn.disabled = !isValid;
                
                if (this.value.length > 0 && !isValid) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            confirmBtn.addEventListener('click', function() {
                if (confirmInput.value === 'CONFIRM') {
                    settingsForm.submit();
                }
            });

            // Reset modal on close
            document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
                confirmInput.value = '';
                confirmBtn.disabled = true;
                confirmInput.classList.remove('is-invalid');
            });
        });
    </script>
    @endpush
</x-app-layout>
