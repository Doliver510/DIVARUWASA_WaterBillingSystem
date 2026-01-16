<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Announcement Details') }}
        </h2>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-{{ $announcement->type_badge }} me-2">
                            {{ ucfirst($announcement->type) }}
                        </span>
                        <h3 class="card-title mb-0">{{ $announcement->title }}</h3>
                    </div>
                    <div class="card-actions">
                        <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-ghost-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                            {{ __('Edit') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="markdown">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode($announcement->createdBy->full_name ?? 'A') }}&background=206bc4&color=fff)"></span>
                        </div>
                        <div class="col">
                            <div class="text-muted small">{{ __('Posted by') }}</div>
                            <div>{{ $announcement->createdBy->full_name ?? 'Unknown' }}</div>
                        </div>
                        <div class="col-auto text-muted">
                            {{ $announcement->created_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Details') }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">{{ __('Status') }}:</dt>
                        <dd class="col-7">
                            @if($announcement->is_active)
                                @if($announcement->ends_at && $announcement->ends_at->isPast())
                                    <span class="badge bg-secondary">{{ __('Expired') }}</span>
                                @elseif($announcement->starts_at->isFuture())
                                    <span class="badge bg-yellow">{{ __('Scheduled') }}</span>
                                @else
                                    <span class="badge bg-green">{{ __('Active') }}</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </dd>
                        
                        <dt class="col-5">{{ __('Audience') }}:</dt>
                        <dd class="col-7">{{ ucfirst($announcement->target_audience) }}</dd>
                        
                        <dt class="col-5">{{ __('Start Date') }}:</dt>
                        <dd class="col-7">{{ $announcement->starts_at->format('M d, Y') }}</dd>
                        
                        <dt class="col-5">{{ __('End Date') }}:</dt>
                        <dd class="col-7">
                            {{ $announcement->ends_at ? $announcement->ends_at->format('M d, Y') : __('No end date') }}
                        </dd>
                        
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

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Actions') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('announcements.edit', $announcement) }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                            {{ __('Edit Announcement') }}
                        </a>
                        <form action="{{ route('announcements.toggle', $announcement) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                @if($announcement->is_active)
                                    {{ __('Deactivate Announcement') }}
                                @else
                                    {{ __('Activate Announcement') }}
                                @endif
                            </button>
                        </form>
                        <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                {{ __('Delete Announcement') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

