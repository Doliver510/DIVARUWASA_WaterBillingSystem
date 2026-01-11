<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Consumers Management') }}
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

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                        </div>
                        <div>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{-- Filter Card --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Search') }}</label>
                            <input type="text" name="search" class="form-control" placeholder="Name or ID No." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Block') }}</label>
                            <select name="block_id" class="form-select">
                                <option value="">{{ __('All Blocks') }}</option>
                                @foreach($blocks as $block)
                                    <option value="{{ $block->id }}" {{ request('block_id') == $block->id ? 'selected' : '' }}>
                                        {{ $block->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="disconnected" {{ request('status') === 'disconnected' ? 'selected' : '' }}>{{ __('Disconnected') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                {{ __('Filter') }}
                            </button>
                            <a href="{{ route('consumers.index') }}" class="btn btn-secondary">{{ __('Clear') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Consumer Accounts') }}</h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-consumer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            {{ __('Add Consumer') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>{{ __('ID No.') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Address') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($consumers as $consumer)
                                <tr>
                                    <td>
                                        <strong class="text-azure">{{ $consumer->id_no }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode($consumer->user->full_name) }}&background=0077b6&color=fff)"></span>
                                            <div>
                                                <div>{{ $consumer->user->full_name }}</div>
                                                <div class="text-secondary small">{{ $consumer->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt me-1">{{ $consumer->block?->name ?? 'N/A' }}</span>
                                        <span class="text-muted">Lot {{ $consumer->lot_number }}</span>
                                    </td>
                                    <td>
                                        @if($consumer->status === 'active')
                                            <span class="badge bg-success-lt">
                                                <span class="badge-dot bg-success me-1"></span>
                                                {{ __('Active') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger-lt">
                                                <span class="badge-dot bg-danger me-1"></span>
                                                {{ __('Disconnected') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('consumers.show', $consumer) }}" class="btn btn-ghost-info btn-icon" title="{{ __('View') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                            </a>
                                            <a href="#" class="btn btn-ghost-primary btn-icon" data-bs-toggle="modal" data-bs-target="#modal-edit-consumer-{{ $consumer->id }}" title="{{ __('Edit') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                            </a>
                                            <a href="#" class="btn btn-ghost-danger btn-icon" data-bs-toggle="modal" data-bs-target="#modal-delete-consumer-{{ $consumer->id }}" title="{{ __('Delete') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                        <p class="mb-0">{{ __('No consumers found.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($consumers->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        {{ $consumers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Consumer Modal --}}
    <div class="modal modal-blur fade" id="modal-add-consumer" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('consumers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add New Consumer') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Account Details Section --}}
                        <div class="mb-3">
                            <h4 class="subheader text-azure">{{ __('Account Details') }}</h4>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ID Number') }}</label>
                                    <input type="text" name="id_no" class="form-control" value="{{ old('id_no') }}" placeholder="{{ $nextIdNo }}" pattern="[0-9]*" inputmode="numeric">
                                    <small class="form-hint">{{ __('Leave blank to auto-generate. Next: ') }}{{ $nextIdNo }}</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label required">{{ __('Block') }}</label>
                                    <select name="block_id" class="form-select" required>
                                        <option value="">{{ __('Select Block') }}</option>
                                        @foreach($blocks as $block)
                                            <option value="{{ $block->id }}" {{ old('block_id') == $block->id ? 'selected' : '' }}>
                                                {{ $block->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label required">{{ __('Lot Number') }}</label>
                                    <input type="number" name="lot_number" class="form-control" value="{{ old('lot_number') }}" required min="1" step="1" placeholder="e.g., 12">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Status') }}</label>
                            <div class="form-selectgroup">
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="status" value="active" class="form-selectgroup-input" checked>
                                    <span class="form-selectgroup-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                        {{ __('Active') }}
                                    </span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="status" value="disconnected" class="form-selectgroup-input" {{ old('status') === 'disconnected' ? 'checked' : '' }}>
                                    <span class="form-selectgroup-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                        {{ __('Disconnected') }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Consumer Information Section --}}
                        <div class="mb-3">
                            <h4 class="subheader text-azure">{{ __('Consumer Information') }}</h4>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label required">{{ __('First Name') }}</label>
                                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required placeholder="Juan">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Middle Name') }}</label>
                                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}" placeholder="Reyes">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label required">{{ __('Last Name') }}</label>
                                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required placeholder="Santos">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Email Address') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="consumer@example.com">
                        </div>
                        <div class="mb-3">
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="alert-title">{{ __('Default Password') }}</h4>
                                        <div class="text-secondary">{{ __('Password will be automatically set to: {LastName}@{IDNo}') }}</div>
                                        <div class="text-secondary small">{{ __('Example: If last name is "Santos" and ID is "014", password will be "Santos@014"') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            {{ __('Create Consumer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Consumer Modals --}}
    @foreach($consumers as $consumer)
        <div class="modal modal-blur fade" id="modal-edit-consumer-{{ $consumer->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form action="{{ route('consumers.update', $consumer) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Edit Consumer') }}: {{ $consumer->id_no }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Account Details Section --}}
                            <div class="mb-3">
                                <h4 class="subheader text-azure">{{ __('Account Details') }}</h4>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('ID Number') }}</label>
                                        <input type="text" name="id_no" class="form-control" value="{{ old('id_no', $consumer->id_no) }}" required pattern="[0-9]*" inputmode="numeric">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Block') }}</label>
                                        <select name="block_id" class="form-select" required>
                                            <option value="">{{ __('Select Block') }}</option>
                                            @foreach($blocks as $block)
                                                <option value="{{ $block->id }}" {{ old('block_id', $consumer->block_id) == $block->id ? 'selected' : '' }}>
                                                    {{ $block->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Lot Number') }}</label>
                                        <input type="number" name="lot_number" class="form-control" value="{{ old('lot_number', $consumer->lot_number) }}" required min="1" step="1">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">{{ __('Status') }}</label>
                                <div class="form-selectgroup">
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="status" value="active" class="form-selectgroup-input" {{ old('status', $consumer->status) === 'active' ? 'checked' : '' }}>
                                        <span class="form-selectgroup-label">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-success" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                            {{ __('Active') }}
                                        </span>
                                    </label>
                                    <label class="form-selectgroup-item">
                                        <input type="radio" name="status" value="disconnected" class="form-selectgroup-input" {{ old('status', $consumer->status) === 'disconnected' ? 'checked' : '' }}>
                                        <span class="form-selectgroup-label">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                            {{ __('Disconnected') }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- Consumer Information Section --}}
                            <div class="mb-3">
                                <h4 class="subheader text-azure">{{ __('Consumer Information') }}</h4>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('First Name') }}</label>
                                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $consumer->user->first_name) }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Middle Name') }}</label>
                                        <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $consumer->user->middle_name) }}">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Last Name') }}</label>
                                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $consumer->user->last_name) }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">{{ __('Email Address') }}</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $consumer->user->email) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="reset_password" value="1">
                                    <span class="form-check-label">{{ __('Reset password to default') }}</span>
                                </label>
                                <small class="form-hint text-secondary">{{ __('New password will be: {LastName}@{IDNo}') }}</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Delete Consumer Modal --}}
        <div class="modal modal-blur fade" id="modal-delete-consumer-{{ $consumer->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-body text-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                        <h3>{{ __('Are you sure?') }}</h3>
                        <div class="text-secondary">
                            {{ __('Do you really want to delete consumer') }} <strong>{{ $consumer->id_no }} - {{ $consumer->user->full_name }}</strong>?<br>
                            <span class="text-danger small">{{ __('This will also delete their user account.') }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn w-100" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <form action="{{ route('consumers.destroy', $consumer) }}" method="POST" class="w-100">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">{{ __('Delete Consumer') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</x-app-layout>
