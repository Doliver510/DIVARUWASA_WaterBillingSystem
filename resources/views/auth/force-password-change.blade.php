<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Change Password') }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Tabler CSS -->
    <link href="{{ asset('tabler/dist/css/tabler.min.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0077b6 0%, #023e8a 100%);
            min-height: 100vh;
        }
        .auth-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container container-tight py-4">
        <div class="card auth-card shadow-lg">
            <div class="card-header bg-primary text-white text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lock mb-2" width="48" height="48" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" />
                    <path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
                    <path d="M8 11v-4a4 4 0 1 1 8 0v4" />
                </svg>
                <h2 class="h3 mb-0">{{ __('Change Your Password') }}</h2>
                <p class="text-white-50 mb-0 mt-1">{{ __('For security, please create a new password') }}</p>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 9v4" />
                                    <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                    <path d="M12 16h.01" />
                                </svg>
                            </div>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.force-change.update') }}" autocomplete="off">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label" for="password">{{ __('New Password') }}</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('Enter new password') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">{{ __('Confirm New Password') }}</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="{{ __('Confirm new password') }}" required>
                    </div>

                    <div class="alert alert-info py-2 mb-3">
                        <div class="d-flex">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                    <path d="M12 9h.01" />
                                    <path d="M11 12h1v4h1" />
                                </svg>
                            </div>
                            <div>
                                <strong>{{ __('Password requirements:') }}</strong><br>
                                {{ __('At least 8 characters') }}
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M5 12l5 5l10 -10" />
                            </svg>
                            {{ __('Set New Password') }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center text-muted py-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <small>{{ __('Need to use a different account?') }} <button type="submit" class="btn btn-link p-0 text-muted">{{ __('Log out') }}</button></small>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Tabler JS -->
    <script src="{{ asset('tabler/dist/js/tabler.min.js') }}" defer></script>
</body>
</html>
