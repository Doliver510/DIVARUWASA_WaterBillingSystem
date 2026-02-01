<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Meter Readings') }}
        </h2>
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
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                        </div>
                        <div>{{ session('error') }}</div>
                    </div>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            </div>
        @endif

        <!-- Entry Form Card (Now on top, horizontal layout) -->
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Record Reading') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('meter-readings.store') }}" method="POST" id="reading-form">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label required">{{ __('Consumer') }}</label>
                                <select name="consumer_id" id="consumer-select" class="form-select" required>
                                    <option value="">{{ __('Select consumer...') }}</option>
                                    @foreach($consumers as $consumer)
                                        <option value="{{ $consumer->id }}" data-previous="{{ $consumer->latest_reading }}">
                                            {{ $consumer->id_no }} - {{ $consumer->user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label">{{ __('Previous') }}</label>
                                <div class="input-group">
                                    <input type="text" id="previous-reading" class="form-control" value="0" readonly>
                                    <span class="input-group-text">cu.m</span>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="form-label required">{{ __('Current Reading') }}</label>
                                <div class="input-group">
                                    <input type="number" name="reading_value" id="current-reading" class="form-control" required min="0" placeholder="Enter">
                                    <span class="input-group-text">cu.m</span>
                                </div>
                            </div>
                            <div class="col-md-1 col-4">
                                <label class="form-label">{{ __('Consumption') }}</label>
                                <div class="input-group">
                                    <input type="text" id="consumption-display" class="form-control bg-light fw-bold text-primary" value="0" readonly>
                                </div>
                            </div>
                            <div class="col-md-2 col-4">
                                <label class="form-label required">{{ __('Date') }}</label>
                                <input type="date" name="reading_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-2 col-4">
                                <label class="form-label required">{{ __('Period') }}</label>
                                <input type="month" name="billing_period" class="form-control" value="{{ $currentPeriod }}" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-10">
                                <input type="text" name="remarks" class="form-control" placeholder="{{ __('Remarks (optional)...') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Readings List (Now full width below the form) -->
        <div class="col-12">
            <!-- Filter Card -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <form action="{{ route('meter-readings.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                        <label class="form-label mb-0 me-2">{{ __('Period:') }}</label>
                        <select name="period" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            @foreach($periods as $period)
                                @php
                                    $periodDate = \Carbon\Carbon::createFromFormat('Y-m', $period);
                                @endphp
                                <option value="{{ $period }}" {{ $currentPeriod === $period ? 'selected' : '' }}>
                                    {{ $periodDate->format('F Y') }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <!-- Readings Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ __('Readings for') }}
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $currentPeriod)->format('F Y') }}
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>{{ __('Consumer') }}</th>
                                <th>{{ __('Previous') }}</th>
                                <th>{{ __('Current') }}</th>
                                <th>{{ __('Consumption') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($readings as $reading)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $reading->consumer->user->full_name ?? 'N/A' }}</strong>
                                            <span class="text-muted small">ID: {{ $reading->consumer->id_no ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($reading->previous_reading) }} cu.m</td>
                                    <td>{{ number_format($reading->reading_value) }} cu.m</td>
                                    <td>
                                        <span class="badge bg-azure-lt fs-5">{{ number_format($reading->consumption) }} cu.m</span>
                                    </td>
                                    <td>{{ $reading->reading_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($reading->is_billed)
                                            <span class="badge bg-green-lt">{{ __('Billed') }}</span>
                                        @else
                                            <span class="badge bg-yellow-lt">{{ __('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reading->canBeEdited())
                                            <div class="btn-list flex-nowrap">
                                                <a href="#" class="btn btn-ghost-primary btn-icon" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $reading->id }}" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                                </a>
                                                <form action="{{ route('meter-readings.destroy', $reading) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this reading?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-ghost-danger btn-icon" title="Delete">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted">{{ __('Locked') }}</span>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                @if($reading->canBeEdited())
                                <div class="modal modal-blur fade" id="modal-edit-{{ $reading->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('meter-readings.update', $reading) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('Edit Reading') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-muted mb-3">
                                                        <strong>{{ $reading->consumer->user->full_name }}</strong> (ID: {{ $reading->consumer->id_no }})
                                                    </p>

                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Previous Reading') }}</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" value="{{ $reading->previous_reading }}" readonly>
                                                            <span class="input-group-text">cu.m</span>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Current Reading') }}</label>
                                                        <div class="input-group">
                                                            <input type="number" name="reading_value" class="form-control" value="{{ $reading->reading_value }}" required min="{{ $reading->previous_reading }}">
                                                            <span class="input-group-text">cu.m</span>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Reading Date') }}</label>
                                                        <input type="date" name="reading_date" class="form-control" value="{{ $reading->reading_date->format('Y-m-d') }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Remarks') }}</label>
                                                        <textarea name="remarks" class="form-control" rows="2">{{ $reading->remarks }}</textarea>
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
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        {{ __('No readings recorded for this period yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const consumerSelect = document.getElementById('consumer-select');
            const previousReadingInput = document.getElementById('previous-reading');
            const currentReadingInput = document.getElementById('current-reading');
            const consumptionDisplay = document.getElementById('consumption-display');

            // Update previous reading when consumer is selected
            consumerSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const previousReading = selectedOption.dataset.previous || 0;
                previousReadingInput.value = previousReading;
                currentReadingInput.min = previousReading;
                updateConsumption();
            });

            // Calculate consumption when current reading changes
            currentReadingInput.addEventListener('input', updateConsumption);

            function updateConsumption() {
                const previous = parseInt(previousReadingInput.value) || 0;
                const current = parseInt(currentReadingInput.value) || 0;
                const consumption = Math.max(0, current - previous);
                consumptionDisplay.value = consumption;
            }
        });
    </script>
</x-app-layout>

