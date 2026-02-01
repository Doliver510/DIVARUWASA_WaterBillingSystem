<x-guest-layout>
    <div class="text-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-warning mb-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /><path d="M12 11m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 12l0 2.5" /></svg>
        <h2 class="h3 mb-1">{{ __('Change Your Password') }}</h2>
        <p class="text-muted">{{ __('For security, please create a new password before continuing.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.force-change.update') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mb-3">
            <div class="alert alert-info py-2">
                <small>
                    <strong>{{ __('Password requirements:') }}</strong><br>
                    {{ __('At least 8 characters') }}
                </small>
            </div>
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Set New Password') }}
        </x-primary-button>
    </form>
</x-guest-layout>
