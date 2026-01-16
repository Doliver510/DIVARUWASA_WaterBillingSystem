<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Create Announcement') }}
        </h2>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-8">
            <form action="{{ route('announcements.store') }}" method="POST" class="card">
                @csrf
                <div class="card-header">
                    <h3 class="card-title">{{ __('New Announcement') }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">{{ __('Title') }}</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title') }}" placeholder="{{ __('Enter announcement title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">{{ __('Content') }}</label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror" 
                                  rows="6" placeholder="{{ __('Enter announcement content...') }}" required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">{{ __('Type') }}</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>
                                    {{ __('Info') }} - {{ __('General information') }}
                                </option>
                                <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>
                                    {{ __('Warning') }} - {{ __('Important notice') }}
                                </option>
                                <option value="urgent" {{ old('type') === 'urgent' ? 'selected' : '' }}>
                                    {{ __('Urgent') }} - {{ __('Critical announcement') }}
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label required">{{ __('Target Audience') }}</label>
                            <select name="target_audience" class="form-select @error('target_audience') is-invalid @enderror" required>
                                <option value="all" {{ old('target_audience', 'all') === 'all' ? 'selected' : '' }}>
                                    {{ __('All Users') }}
                                </option>
                                <option value="consumers" {{ old('target_audience') === 'consumers' ? 'selected' : '' }}>
                                    {{ __('Consumers Only') }}
                                </option>
                                <option value="staff" {{ old('target_audience') === 'staff' ? 'selected' : '' }}>
                                    {{ __('Staff Only') }}
                                </option>
                            </select>
                            @error('target_audience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">{{ __('Start Date') }}</label>
                            <input type="date" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror" 
                                   value="{{ old('starts_at', now()->format('Y-m-d')) }}" required>
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('End Date') }}</label>
                            <input type="date" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" 
                                   value="{{ old('ends_at') }}" placeholder="{{ __('Leave empty for no end date') }}">
                            <small class="form-hint">{{ __('Leave empty if the announcement has no end date.') }}</small>
                            @error('ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="send_email" value="1" class="form-check-input" 
                                   {{ old('send_email') ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('Send email notification to target audience') }}</span>
                        </label>
                        <small class="form-hint text-muted">
                            {{ __('If checked, an email will be sent to all users in the target audience. Make sure email is configured.') }}
                        </small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('announcements.index') }}" class="btn me-2">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 14l11 -11" /><path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" /></svg>
                        {{ __('Create Announcement') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Tips') }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h4 class="text-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                            {{ __('Info') }}
                        </h4>
                        <p class="text-muted small">{{ __('Use for general updates, news, and non-critical information.') }}</p>
                    </div>
                    <div class="mb-3">
                        <h4 class="text-yellow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                            {{ __('Warning') }}
                        </h4>
                        <p class="text-muted small">{{ __('Use for scheduled maintenance, service interruptions, or important reminders.') }}</p>
                    </div>
                    <div class="mb-3">
                        <h4 class="text-red">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>
                            {{ __('Urgent') }}
                        </h4>
                        <p class="text-muted small">{{ __('Use for emergency situations, critical system changes, or immediate action required.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

