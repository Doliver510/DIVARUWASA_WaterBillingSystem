<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Consumer Details') }}
        </h2>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <span class="avatar avatar-xl mb-3" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode($consumer->user->full_name) }}&background=0077b6&color=fff&size=128)"></span>
                    <h3 class="mb-0">{{ $consumer->user->full_name }}</h3>
                    <p class="text-muted mb-1">{{ $consumer->user->formal_name }}</p>
                    <p class="text-muted">{{ $consumer->user->email }}</p>
                    @if($consumer->status === 'active')
                        <span class="badge bg-success">{{ __('Active') }}</span>
                    @else
                        <span class="badge bg-danger">{{ __('Disconnected') }}</span>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex">
                        <a href="{{ route('consumers.index') }}" class="btn btn-outline-secondary w-100 me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                            {{ __('Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Account Information') }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-5">{{ __('ID Number') }}:</dt>
                        <dd class="col-7"><strong class="text-azure">{{ $consumer->id_no }}</strong></dd>

                        <dt class="col-5">{{ __('First Name') }}:</dt>
                        <dd class="col-7">{{ $consumer->user->first_name }}</dd>

                        <dt class="col-5">{{ __('Middle Name') }}:</dt>
                        <dd class="col-7">{{ $consumer->user->middle_name ?? '-' }}</dd>

                        <dt class="col-5">{{ __('Last Name') }}:</dt>
                        <dd class="col-7">{{ $consumer->user->last_name }}</dd>

                        <dt class="col-5">{{ __('Address') }}:</dt>
                        <dd class="col-7">{{ $consumer->address }}</dd>

                        <dt class="col-5">{{ __('Status') }}:</dt>
                        <dd class="col-7">
                            @if($consumer->status === 'active')
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Disconnected') }}</span>
                            @endif
                        </dd>

                        <dt class="col-5">{{ __('Registered') }}:</dt>
                        <dd class="col-7">{{ $consumer->created_at->format('M d, Y h:i A') }}</dd>

                        <dt class="col-5">{{ __('Last Updated') }}:</dt>
                        <dd class="col-7">{{ $consumer->updated_at->format('M d, Y h:i A') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Placeholder for future billing info -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Billing Summary') }}</h3>
                </div>
                <div class="card-body text-center text-muted py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                    <p>{{ __('Billing information will be available after Phase 5 implementation.') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
