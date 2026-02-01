<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Tabler CSS -->
        <link href="{{ asset('tabler/dist/css/tabler.min.css') }}" rel="stylesheet">

        <!-- Custom Water Theme Override -->
        <style>
            :root {
                --tblr-sidebar-bg: linear-gradient(180deg, #0077b6 0%, #023e8a 100%);
            }
            .navbar-vertical {
                background: linear-gradient(180deg, #0077b6 0%, #023e8a 100%) !important;
            }
            .navbar-vertical .navbar-brand-text {
                font-weight: 600;
            }

            /* Sidebar Text Opacity & Colors */
            .navbar-vertical .nav-link {
                color: rgba(255, 255, 255, 0.75) !important;
                transition: all 0.25s ease;
            }
            .navbar-vertical .nav-link:hover {
                color: rgba(255, 255, 255, 1) !important;
                background: rgba(255, 255, 255, 0.1);
                transform: translateX(4px);
            }
            .navbar-vertical .nav-link.active {
                color: #fff !important;
                background: rgba(255, 255, 255, 0.15) !important;
            }
            .navbar-vertical .nav-link-icon {
                opacity: 0.8;
                transition: all 0.25s ease;
            }
            .navbar-vertical .nav-link:hover .nav-link-icon {
                opacity: 1;
                transform: scale(1.1);
            }
            .navbar-vertical .nav-link-title {
                opacity: 0.9;
            }
            .navbar-vertical .nav-link:hover .nav-link-title {
                opacity: 1;
            }

            /* Dropdown Menu Animation */
            .navbar-vertical .dropdown-menu {
                animation: slideDown 0.25s ease-out;
                transform-origin: top;
                border: none;
                background: rgba(0, 0, 0, 0.2);
                backdrop-filter: blur(10px);
            }
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-8px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Dropdown Items */
            .navbar-vertical .dropdown-item {
                color: rgba(255, 255, 255, 0.7) !important;
                transition: all 0.2s ease;
                border-radius: 4px;
                margin: 2px 8px;
                padding: 8px 12px;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .navbar-vertical .dropdown-item:hover {
                color: #fff !important;
                background: rgba(255, 255, 255, 0.15) !important;
                transform: translateX(4px);
            }
            .navbar-vertical .dropdown-item-icon {
                width: 1.25rem;
                height: 1.25rem;
                opacity: 0.7;
                transition: all 0.2s ease;
            }
            .navbar-vertical .dropdown-item:hover .dropdown-item-icon {
                opacity: 1;
            }

            /* Dropdown Toggle Arrow Animation */
            .navbar-vertical .dropdown-toggle::after {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                opacity: 0.6;
            }
            .navbar-vertical .dropdown-toggle:hover::after {
                opacity: 1;
            }
            .navbar-vertical .nav-item.dropdown .dropdown-toggle[aria-expanded="true"]::after {
                transform: rotate(180deg);
                opacity: 1;
            }

            /* Nav Item Stagger Animation on Load */
            .navbar-vertical .nav-item {
                animation: fadeInLeft 0.4s ease backwards;
            }
            .navbar-vertical .nav-item:nth-child(1) { animation-delay: 0.05s; }
            .navbar-vertical .nav-item:nth-child(2) { animation-delay: 0.1s; }
            .navbar-vertical .nav-item:nth-child(3) { animation-delay: 0.15s; }
            .navbar-vertical .nav-item:nth-child(4) { animation-delay: 0.2s; }
            .navbar-vertical .nav-item:nth-child(5) { animation-delay: 0.25s; }
            .navbar-vertical .nav-item:nth-child(6) { animation-delay: 0.3s; }
            .navbar-vertical .nav-item:nth-child(7) { animation-delay: 0.35s; }
            .navbar-vertical .nav-item:nth-child(8) { animation-delay: 0.4s; }
            .navbar-vertical .nav-item:nth-child(9) { animation-delay: 0.45s; }
            .navbar-vertical .nav-item:nth-child(10) { animation-delay: 0.5s; }

            @keyframes fadeInLeft {
                from {
                    opacity: 0;
                    transform: translateX(-12px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            /* Icon Pulse on Hover */
            .navbar-vertical .nav-link:hover .nav-link-icon svg {
                animation: iconPulse 0.4s ease;
            }
            @keyframes iconPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.15); }
            }

            /* Brand Animation */
            .navbar-vertical .navbar-brand {
                transition: transform 0.3s ease;
            }
            .navbar-vertical .navbar-brand:hover {
                transform: scale(1.02);
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="page">
            <!-- Sidebar -->
            @include('layouts.partials.sidebar')

            <!-- Navbar + Content -->
            <div class="page-wrapper">
                <!-- Top Navbar -->
                @include('layouts.partials.header')

                <!-- Page Header -->
                @isset($header)
                <div class="page-header d-print-none">
                    <div class="container-xl">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                {{ $header }}
                            </div>
                        </div>
                    </div>
                </div>
                @endisset

                <!-- Page Body -->
                <div class="page-body">
                    <div class="container-xl">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Footer -->
                <footer class="footer footer-transparent d-print-none">
                    <div class="container-xl">
                        <div class="row text-center align-items-center flex-row-reverse">
                            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                                <ul class="list-inline list-inline-dots mb-0">
                                    <li class="list-inline-item">
                                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Tabler JS -->
        <script src="{{ asset('tabler/dist/js/tabler.min.js') }}" defer></script>

        <!-- Page-specific scripts -->
        @stack('scripts')
    </body>
</html>
