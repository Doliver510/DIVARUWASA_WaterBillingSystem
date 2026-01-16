<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Announcements') }}
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

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('All Announcements') }}</h3>
                    <div class="card-actions">
                        <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            {{ __('New Announcement') }}
                        </a>
                    </div>
                </div>
                <div class="card-body border-bottom py-3">
                    <form method="GET" class="row g-2">
                        <div class="col-auto">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>{{ __('Info') }}</option>
                                <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>{{ __('Warning') }}</option>
                                <option value="urgent" {{ request('type') === 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                            </select>
                        </div>
                        @if(request()->hasAny(['status', 'type']))
                            <div class="col-auto">
                                <a href="{{ route('announcements.index') }}" class="btn">{{ __('Clear Filters') }}</a>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Audience') }}</th>
                                <th>{{ __('Date Range') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $announcement)
                                <tr>
                                    <td>
                                        <a href="{{ route('announcements.show', $announcement) }}" class="text-reset">
                                            {{ $announcement->title }}
                                        </a>
                                        <div class="text-muted small">
                                            {{ Str::limit($announcement->content, 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $announcement->type_badge }}">
                                            {{ ucfirst($announcement->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ ucfirst($announcement->target_audience) }}
                                    </td>
                                    <td>
                                        {{ $announcement->starts_at->format('M d, Y') }}
                                        @if($announcement->ends_at)
                                            - {{ $announcement->ends_at->format('M d, Y') }}
                                        @else
                                            - {{ __('No end date') }}
                                        @endif
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-ghost-secondary btn-icon" data-bs-toggle="dropdown">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('announcements.show', $announcement) }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                                    {{ __('View') }}
                                                </a>
                                                <a class="dropdown-item" href="{{ route('announcements.edit', $announcement) }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                                    {{ __('Edit') }}
                                                </a>
                                                <form action="{{ route('announcements.toggle', $announcement) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item">
                                                        @if($announcement->is_active)
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M3 3l18 18" /><path d="M6.3 6.3c-1.6 1.6 -2.7 3.4 -3.3 5.7c2.4 4 5.4 6 9 6c1.8 0 3.4 -.5 4.9 -1.6" /><path d="M10 10c-.6 .6 -.9 1.3 -.9 2" /><path d="M14 14c.6 -.6 .9 -1.3 .9 -2" /><path d="M17.7 17.7c1.6 -1.6 2.7 -3.4 3.3 -5.7c-2.4 -4 -5.4 -6 -9 -6c-1.8 0 -3.4 .5 -4.9 1.6" /></svg>
                                                            {{ __('Deactivate') }}
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                                            {{ __('Activate') }}
                                                        @endif
                                                    </button>
                                                </form>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        {{ __('No announcements found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($announcements->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $announcements->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

