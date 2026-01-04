<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    @php
        // Friendly display names for settings (internal key => display label)
        $friendlyNames = [
            'base_charge' => 'Minimum Charge',
            'base_charge_covers_cubic' => 'Minimum Covers (cu.m)',
            'penalty_fee' => 'Penalty Fee',
            'registration_fee' => 'Registration Fee',
            'payment_due_days' => 'Payment Due Days',
            'reconnection_fee' => 'Reconnection Fee',
        ];
    @endphp

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

            <form action="{{ route('settings.update') }}" method="POST" class="card">
                @csrf
                <div class="card-header">
                    <h3 class="card-title">{{ __('Configuration') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($settings as $setting)
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ $friendlyNames[$setting->key] ?? Str::title(str_replace('_', ' ', $setting->key)) }}
                                    <span class="form-label-description text-muted">
                                        {{ $setting->description }}
                                    </span>
                                </label>
                                <div class="input-group">
                                    @if($setting->type === 'currency')
                                        <span class="input-group-text">₱</span>
                                    @endif
                                    <input type="number" 
                                           step="{{ $setting->type === 'currency' ? '0.01' : '1' }}" 
                                           name="settings[{{ $setting->key }}]" 
                                           class="form-control" 
                                           value="{{ $setting->value }}"
                                           required>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                        {{ __('Save Settings') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
