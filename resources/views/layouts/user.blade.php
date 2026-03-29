<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('storage/favicon.ico') }}">
    
    <title>{{ $title ?? 'Lentera AI' }}</title>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('lentera_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            background-color: var(--bs-body-tertiary);
            margin: 0;
            padding: 0;
        }

        .main-wrapper {
            height: 100vh;
            display: flex;
        }

        @media (max-width: 991.98px) {
            .main-wrapper {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    
    <main class="main-wrapper">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>