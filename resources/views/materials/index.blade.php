<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Materials Inventory') }}
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

            <!-- Materials Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('All Materials') }}</h3>
                    <div class="card-actions">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-material">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            {{ __('Add Material') }}
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th>{{ __('Unit Price') }}</th>
                                <th>{{ __('Stock') }}</th>
                                <th>{{ __('Reorder Level') }}</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($materials as $material)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $material->name }}</strong>
                                            @if($material->description)
                                                <span class="text-muted small">{{ Str::limit($material->description, 50) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $material->unit }}</td>
                                    <td>₱{{ number_format($material->unit_price, 2) }}</td>
                                    <td>
                                        @if($material->isLowStock())
                                            <span class="badge bg-red-lt">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                                                {{ $material->stock_quantity }} {{ $material->unit }}
                                            </span>
                                        @else
                                            <span class="badge bg-green-lt">{{ $material->stock_quantity }} {{ $material->unit }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $material->reorder_level }} {{ $material->unit }}</td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="#" class="btn btn-ghost-success btn-icon" data-bs-toggle="modal" data-bs-target="#modal-add-stock-{{ $material->id }}" title="Add Stock">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                                            </a>
                                            <a href="#" class="btn btn-ghost-primary btn-icon" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $material->id }}" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                            </a>
                                            <form action="{{ route('materials.destroy', $material) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this material?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost-danger btn-icon" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal modal-blur fade" id="modal-edit-{{ $material->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('materials.update', $material) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('Edit Material') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Name') }}</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $material->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Description') }}</label>
                                                        <textarea name="description" class="form-control" rows="2">{{ $material->description }}</textarea>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label required">{{ __('Unit') }}</label>
                                                            <input type="text" name="unit" class="form-control" value="{{ $material->unit }}" required placeholder="pcs, meters, kg">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label required">{{ __('Unit Price (₱)') }}</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">₱</span>
                                                                <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ $material->unit_price }}" required min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Reorder Level') }}</label>
                                                        <input type="number" name="reorder_level" class="form-control" value="{{ $material->reorder_level }}" required min="0">
                                                        <small class="form-hint">{{ __('You will see a warning when stock falls to or below this level.') }}</small>
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

                                <!-- Add Stock Modal -->
                                <div class="modal modal-blur fade" id="modal-add-stock-{{ $material->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('materials.add-stock', $material) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('Add Stock') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-muted">{{ $material->name }}</p>
                                                    <p class="mb-3">Current stock: <strong>{{ $material->stock_quantity }} {{ $material->unit }}</strong></p>
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Quantity to Add') }}</label>
                                                        <div class="input-group">
                                                            <input type="number" name="quantity" class="form-control" required min="1" placeholder="0">
                                                            <span class="input-group-text">{{ $material->unit }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Notes (optional)') }}</label>
                                                        <input type="text" name="notes" class="form-control" placeholder="e.g., Purchased Jan 2026">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <button type="submit" class="btn btn-success">{{ __('Add Stock') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        {{ __('No materials found. Add one to get started.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Material Modal -->
    <div class="modal modal-blur fade" id="modal-add-material" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('materials.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add Material') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Name') }}</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., PVC Pipe 1/2&quot;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional details..."></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label required">{{ __('Unit') }}</label>
                                <input type="text" name="unit" class="form-control" required placeholder="pcs, meters, kg">
                            </div>
                            <div class="col-6">
                                <label class="form-label required">{{ __('Unit Price (₱)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" name="unit_price" class="form-control" required min="0" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label required">{{ __('Initial Stock') }}</label>
                                <input type="number" name="stock_quantity" class="form-control" required min="0" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label required">{{ __('Reorder Level') }}</label>
                                <input type="number" name="reorder_level" class="form-control" required min="0" value="10">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Add Material') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

