<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Meter Reader Block Assignments') }}
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

            <!-- Info Card -->
            <div class="card mb-3 bg-azure-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-azure me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                        </span>
                        <div>
                            <div class="font-weight-medium">{{ __('Assign Blocks to Meter Readers') }}</div>
                            <div class="text-muted">
                                {{ __('Meter readers can be assigned to multiple blocks. They will see consumers from their assigned blocks when recording readings.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($meterReaders->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="text-muted">{{ __('No meter readers found. Add users with the "Meter Reader" role first.') }}</div>
                    </div>
                </div>
            @else
                <!-- Meter Readers Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Meter Readers') }}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Assigned Blocks') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($meterReaders as $reader)
                                    <tr>
                                        <td>
                                            <strong>{{ $reader->full_name }}</strong>
                                        </td>
                                        <td class="text-muted">{{ $reader->email }}</td>
                                        <td>
                                            @if($reader->assignedBlocks->isNotEmpty())
                                                @foreach($reader->assignedBlocks as $block)
                                                    <span class="badge bg-blue-lt me-1">{{ $block->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">{{ __('No blocks assigned') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-assign-{{ $reader->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                                {{ __('Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Assignment Modals -->
    @foreach($meterReaders as $reader)
        <div class="modal modal-blur fade" id="modal-assign-{{ $reader->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form action="{{ route('settings.block-assignments.update', $reader) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Assign Blocks to') }} {{ $reader->full_name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">{{ __('Select the blocks this meter reader is responsible for:') }}</p>
                            
                            @if($blocks->isEmpty())
                                <div class="alert alert-warning">
                                    {{ __('No active blocks available. Please add blocks first.') }}
                                </div>
                            @else
                                <div class="row">
                                    @foreach($blocks as $block)
                                        <div class="col-6 mb-2">
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" name="block_ids[]" value="{{ $block->id }}"
                                                    {{ $reader->assignedBlocks->contains($block->id) ? 'checked' : '' }}>
                                                <span class="form-check-label">{{ $block->name }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Save Assignments') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</x-app-layout>

