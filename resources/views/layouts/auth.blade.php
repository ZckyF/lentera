<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ asset('storage/favicon.ico') }}">
        <title>{{ $title ?? config('app.name', 'Lentera') }}</title>

        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-12 col-lg-7">
                <div class="card shadow-sm border-0 rounded-4 mt-4">
                    <div class="text-center">
                        <img 
                            src="{{ asset('storage/polnes-logo.png') }}" 
                            alt="Logo Politeknik Negeri Samarinda" 
                            class="img-fluid mb-4 mt-3" 
                            style="max-height: 100px; width: auto;"
                        >
                        <p class="px-4" style="font-size: 0.9rem; line-height: 1.4;">
                            <strong class="text-primary">LENTERA:</strong> Layanan Terpadu Referensi Akademik berbasis AI untuk Mahasiswa Politeknik Negeri Samarinda.
                        </p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        {{ $slot }}
                    </div>
                </div>
    
            </div>
        </div>
    </div>
        @livewireScripts
    </body>
</html>

