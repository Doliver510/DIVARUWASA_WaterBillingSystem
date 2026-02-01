<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <span class="avatar avatar-xl mb-3" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name) }}&background=0077b6&color=fff&size=128)"></span>
                    <h3 class="mb-0">{{ Auth::user()->full_name }}</h3>
                    <p class="text-muted">{{ Auth::user()->email }}</p>
                    <span class="badge bg-azure-lt">{{ Auth::user()->role->name }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- Profile Information --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Profile Information') }}</h3>
                </div>
                <div class="card-body">
                    {{-- Read-only Name Display --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }}</label>
                        <div class="form-control-plaintext">
                            <strong>{{ $user->full_name }}</strong>
                        </div>
                        <small class="text-muted">{{ __('Contact an administrator to change your name.') }}</small>
                    </div>

                    <hr class="my-3">

                    <p class="text-muted mb-3">{{ __('Update your email address below.') }}</p>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="mb-3">
                            <label class="form-label" for="email">{{ __('Email') }}</label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" placeholder="email@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('Leave blank if you don\'t want to receive email notifications.') }}</small>

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-2">
                                    <p class="text-warning small">
                                        {{ __('Your email address is unverified.') }}
                                        <button form="send-verification" class="btn btn-link btn-sm p-0">
                                            {{ __('Click here to re-send the verification email.') }}
                                        </button>
                                    </p>
                                    @if (session('status') === 'verification-link-sent')
                                        <p class="text-success small">
                                            {{ __('A new verification link has been sent to your email address.') }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            {{ __('Save Email') }}
                        </button>

                        @if (session('status') === 'profile-updated')
                            <span class="text-success ms-2">{{ __('Saved.') }}</span>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Update Password --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Update Password') }}</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>

                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label class="form-label required" for="current_password">{{ __('Current Password') }}</label>
                            <input type="password" id="current_password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label class="form-label required" for="password">{{ __('New Password') }}</label>
                                <input type="password" id="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" required>
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label required" for="password_confirmation">{{ __('Confirm Password') }}</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            {{ __('Save') }}
                        </button>

                        @if (session('status') === 'password-updated')
                            <span class="text-success ms-2">{{ __('Saved.') }}</span>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
