<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Block Management') }}
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
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                        </div>
                        <div>{{ session('error') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            <!-- Info Card -->
            <div class="card mb-3 bg-azure-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-azure me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                        </span>
                        <div>
                            <div class="font-weight-medium">{{ __('Block/Area Management') }}</div>
                            <div class="text-muted">
                                {{ __('Manage blocks and areas where consumers are located. Examples: "Block 5", "Sitio Malabon", "Phase 2".') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blocks Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Blocks / Areas') }}</h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            {{ __('Add Block') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th class="text-center">{{ __('Consumers') }}</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blocks as $block)
                                <tr>
                                    <td>
                                        <strong>{{ $block->name }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-blue-lt">{{ $block->consumers_count }}</span>
                                    </td>
                                    <td>
                                        @if($block->consumers_count === 0)
                                            <form action="{{ route('settings.blocks.destroy', $block) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this block?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">{{ __('Has consumers') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        {{ __('No blocks configured. Add your first block to get started.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Block Modal -->
    <div class="modal modal-blur fade" id="modal-add-block" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('settings.blocks.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add New Block / Area') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required placeholder="e.g., Block 5, Sitio Malabon">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-hint">{{ __('Enter the block or area name as it should appear in addresses.') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Add Block') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
