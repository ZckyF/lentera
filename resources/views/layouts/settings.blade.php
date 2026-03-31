<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('storage/favicon.ico') }}">
    <title>{{ $title ?? 'Profil - Lentera' }}</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('lentera_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            
            document.addEventListener('DOMContentLoaded', () => {
                updateIcon(savedTheme);
            });
        })();
    </script>
    @vite(['resources/scss/app.scss','resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-light-subtle">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ auth()->user()->role !== 'admin' ? route('chatbot') : route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-circle shadow-sm" title="Kembali">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    
                    <h4 class="fw-bold mb-0">Pengaturan Akun</h4>

                    <button class="btn btn-outline-secondary shadow-sm rounded-pill px-3" id="themeToggle" onclick="toggleTheme()">
                        <i id="themeIcon" class="bi bi-sun"></i>
                    </button>
                </div>

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-5">
                            <img src="{{ asset('storage/polnes-logo.png') }}" alt="Logo" class="img-fluid mb-3" style="max-height: 60px;">
                            <p class="text-muted small px-lg-5">
                                Perbarui informasi profil Anda untuk memastikan data referensi akademik tetap akurat di platform <span class=" fw-bold">Lentera</span>.
                            </p>
                        </div>

                        {{ $slot }}
                    </div>
                </div>

                <div class="text-center mt-4 text-muted small">
                    © 2026 LENTERA - Politeknik Negeri Samarinda. All Rights Reserved.
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
    
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('lentera_theme', newTheme);
            updateIcon(newTheme);
        }

        function updateIcon(theme) {
            const icon = document.getElementById('themeIcon');
            icon.className = theme === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
        }
    </script>
</body>
</html>