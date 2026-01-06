<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('maintenance-requests.index') }}" class="btn btn-ghost-secondary btn-icon me-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
            </a>
            <h2 class="page-title">
                {{ __('Request #') }}{{ $request->id }}
            </h2>
            <span class="badge bg-{{ $request->status_color }}-lt ms-2">{{ $request->status_label }}</span>
        </div>
    </x-slot>

    <div class="row row-cards">
        @if(session('success'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        </div>
                        <div>{{ session('success') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>{{ session('error') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            </div>
        @endif

        <!-- Request Details -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Request Details') }}</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">{{ __('Consumer') }}</div>
                            <div class="datagrid-content">
                                <strong>{{ $request->consumer->user->full_name ?? 'N/A' }}</strong>
                                <br>
                                <span class="text-muted">ID: {{ $request->consumer->id_no ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{{ __('Address') }}</div>
                            <div class="datagrid-content">{{ $request->consumer->address ?? 'N/A' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{{ __('Request Type') }}</div>
                            <div class="datagrid-content">{{ $request->request_type_label }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">{{ __('Requested At') }}</div>
                            <div class="datagrid-content">{{ $request->requested_at->format('M d, Y h:i A') }}</div>
                        </div>
                        @if($request->completed_at)
                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ __('Completed At') }}</div>
                                <div class="datagrid-content">{{ $request->completed_at->format('M d, Y h:i A') }}</div>
                            </div>
                        @endif
                        @if($request->requestedByUser)
                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ __('Created By') }}</div>
                                <div class="datagrid-content">{{ $request->requestedByUser->full_name }}</div>
                            </div>
                        @endif
                        @if($request->payment_option_label)
                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ __('Payment Option') }}</div>
                                <div class="datagrid-content">
                                    <span class="badge bg-{{ $request->payment_option === 'pay_now' ? 'green' : 'blue' }}-lt">
                                        {{ $request->payment_option_label }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($request->description)
                        <div class="mt-3">
                            <div class="datagrid-title mb-1">{{ __('Description') }}</div>
                            <p class="text-muted">{{ $request->description }}</p>
                        </div>
                    @endif

                    @if($request->remarks)
                        <div class="mt-3">
                            <div class="datagrid-title mb-1">{{ __('Staff Remarks') }}</div>
                            <p class="text-muted">{{ $request->remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Materials Used -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Materials Used') }}</h3>
                    @if($request->canAddMaterials() && in_array(auth()->user()->role->slug, ['admin', 'maintenance-staff']))
                        <div class="card-actions">
                            <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-add-material">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                                {{ __('Add Material') }}
                            </a>
                        </div>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>{{ __('Material') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Unit Price') }}</th>
                                <th>{{ __('Subtotal') }}</th>
                                @if($request->canAddMaterials())
                                    <th class="w-1"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($request->maintenanceMaterials as $mm)
                                <tr>
                                    <td>{{ $mm->material->name }}</td>
                                    <td>{{ $mm->quantity }} {{ $mm->material->unit }}</td>
                                    <td>₱{{ number_format($mm->unit_price, 2) }}</td>
                                    <td>₱{{ number_format($mm->subtotal, 2) }}</td>
                                    @if($request->canAddMaterials() && in_array(auth()->user()->role->slug, ['admin', 'maintenance-staff']))
                                        <td>
                                            <form action="{{ route('maintenance-requests.remove-material', [$request, $mm]) }}" method="POST" onsubmit="return confirm('Remove this material?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost-danger btn-icon btn-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $request->canAddMaterials() ? 5 : 4 }}" class="text-center text-muted py-3">
                                        {{ __('No materials used yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">{{ __('Total Material Cost:') }}</th>
                                <th>₱{{ number_format($request->total_material_cost, 2) }}</th>
                                @if($request->canAddMaterials())
                                    <th></th>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="col-lg-4">
            @if(in_array(auth()->user()->role->slug, ['admin', 'maintenance-staff']))
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Actions') }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('maintenance-requests.update-status', $request) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label">{{ __('Update Status') }}</label>
                                <select name="status" class="form-select" id="status-select" onchange="togglePaymentOption()">
                                    @if($request->status === 'pending')
                                        <option value="pending" selected>{{ __('Pending') }}</option>
                                        <option value="in_progress">{{ __('Mark as In Progress') }}</option>
                                        <option value="cancelled">{{ __('Cancel Request') }}</option>
                                    @elseif($request->status === 'in_progress')
                                        <option value="in_progress" selected>{{ __('In Progress') }}</option>
                                        <option value="completed">{{ __('Mark as Completed') }}</option>
                                        <option value="cancelled">{{ __('Cancel Request') }}</option>
                                    @else
                                        <option value="{{ $request->status }}" selected disabled>{{ $request->status_label }}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="mb-3" id="payment-option-group" style="display: none;">
                                <label class="form-label required">{{ __('Payment Option') }}</label>
                                <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="payment_option" value="pay_now" class="form-selectgroup-input">
                                        <div class="form-selectgroup-label d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </div>
                                            <div>
                                                <strong>{{ __('Pay Now') }}</strong>
                                                <div class="text-muted small">{{ __('Consumer will pay at the cashier') }}</div>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="payment_option" value="charge_to_bill" class="form-selectgroup-input">
                                        <div class="form-selectgroup-label d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </div>
                                            <div>
                                                <strong>{{ __('Charge to Bill') }}</strong>
                                                <div class="text-muted small">{{ __('Add to consumer\'s next water bill') }}</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('Remarks') }}</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="{{ __('Optional staff notes...') }}">{{ $request->remarks }}</textarea>
                            </div>

                            @if($request->canBeEdited())
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('Update Request') }}
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            @endif

            <!-- Request Summary -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Summary') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('Materials Used:') }}</span>
                        <span>{{ $request->maintenanceMaterials->count() }} {{ __('items') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('Total Cost:') }}</span>
                        <strong class="fs-4">₱{{ number_format($request->total_material_cost, 2) }}</strong>
                    </div>
                    <hr>
                    <p class="text-muted small mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-info-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                        {{ __('Labor fees are paid directly to the technician and not tracked in this system.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Material Modal -->
    @if($request->canAddMaterials())
        <div class="modal modal-blur fade" id="modal-add-material" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form action="{{ route('maintenance-requests.add-material', $request) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Add Material') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">{{ __('Material') }}</label>
                                <select name="material_id" class="form-select" required>
                                    <option value="">{{ __('Select material...') }}</option>
                                    @foreach($materials as $material)
                                        <option value="{{ $material->id }}" data-unit="{{ $material->unit }}" data-price="{{ $material->unit_price }}" data-stock="{{ $material->stock_quantity }}">
                                            {{ $material->name }} (Stock: {{ $material->stock_quantity }} {{ $material->unit }}) - ₱{{ number_format($material->unit_price, 2) }}/{{ $material->unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">{{ __('Quantity') }}</label>
                                <input type="number" name="quantity" class="form-control" required min="1" value="1">
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
    @endif

    <script>
        function togglePaymentOption() {
            const statusSelect = document.getElementById('status-select');
            const paymentGroup = document.getElementById('payment-option-group');
            if (statusSelect && paymentGroup) {
                paymentGroup.style.display = statusSelect.value === 'completed' ? 'block' : 'none';
            }
        }
        // Run on page load
        document.addEventListener('DOMContentLoaded', togglePaymentOption);
    </script>
</x-app-layout>

