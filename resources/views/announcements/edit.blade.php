<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Edit Announcement') }}
        </h2>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-8">
            <form action="{{ route('announcements.update', $announcement) }}" method="POST" class="card">
                @csrf
                @method('PUT')
                <div class="card-header">
                    <h3 class="card-title">{{ __('Edit Announcement') }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">{{ __('Title') }}</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $announcement->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">{{ __('Content') }}</label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror" 
                                  rows="6" required>{{ old('content', $announcement->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">{{ __('Type') }}</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="info" {{ old('type', $announcement->type) === 'info' ? 'selected' : '' }}>
                                    {{ __('Info') }}
                                </option>
                                <option value="warning" {{ old('type', $announcement->type) === 'warning' ? 'selected' : '' }}>
                                    {{ __('Warning') }}
                                </option>
                                <option value="urgent" {{ old('type', $announcement->type) === 'urgent' ? 'selected' : '' }}>
                                    {{ __('Urgent') }}
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label required">{{ __('Target Audience') }}</label>
                            <select name="target_audience" class="form-select @error('target_audience') is-invalid @enderror" required>
                                <option value="all" {{ old('target_audience', $announcement->target_audience) === 'all' ? 'selected' : '' }}>
                                    {{ __('All Users') }}
                                </option>
                                <option value="consumers" {{ old('target_audience', $announcement->target_audience) === 'consumers' ? 'selected' : '' }}>
                                    {{ __('Consumers Only') }}
                                </option>
                                <option value="staff" {{ old('target_audience', $announcement->target_audience) === 'staff' ? 'selected' : '' }}>
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
                                   value="{{ old('starts_at', $announcement->starts_at->format('Y-m-d')) }}" required>
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('End Date') }}</label>
                            <input type="date" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" 
                                   value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d')) }}">
                            <small class="form-hint">{{ __('Leave empty if the announcement has no end date.') }}</small>
                            @error('ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                   {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                            <span class="form-check-label">{{ __('Active') }}</span>
                        </label>
                        <small class="form-hint text-muted">
                            {{ __('Uncheck to hide this announcement from users.') }}
                        </small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('announcements.index') }}" class="btn me-2">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Announcement Info') }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">{{ __('Created') }}:</dt>
                        <dd class="col-7">{{ $announcement->created_at->format('M d, Y h:i A') }}</dd>
                        
                        <dt class="col-5">{{ __('Created By') }}:</dt>
                        <dd class="col-7">{{ $announcement->createdBy->full_name ?? 'N/A' }}</dd>
                        
                        <dt class="col-5">{{ __('Last Updated') }}:</dt>
                        <dd class="col-7">{{ $announcement->updated_at->format('M d, Y h:i A') }}</dd>
                        
                        <dt class="col-5">{{ __('Email Sent') }}:</dt>
                        <dd class="col-7">
                            @if($announcement->send_email)
                                <span class="badge bg-green">{{ __('Yes') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('No') }}</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

