<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'LENTERA') }}</title>

        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="admin-font">
        <div id="adminLayout" class="admin-layout">
            <div id="sidebarBackdrop" class="admin-sidebar-backdrop"></div>

            <aside class="admin-sidebar bg-primary text-white p-3">
                <a href="{{ route('dashboard') }}" wire:navigate class="d-flex align-items-center gap-2 mb-3 text-white text-decoration-none">
                    <i class="bi bi-lamp-fill fs-5"></i>
                    <span class="sidebar-brand-text fw-semibold">LENTERA</span>
                </a>

                <hr class="border-light opacity-25">

                <nav class="nav nav-pills flex-column gap-1">
                    <a
                        href="{{ route('dashboard') }}"
                        wire:navigate
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    >
                        <i class="bi bi-house"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>

                    <a
                        href="{{ route('admin.users') }}"
                        wire:navigate
                        class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"
                    >
                        <i class="bi bi-people"></i>
                        <span class="sidebar-text">Pengguna</span>
                    </a>

                    <a
                        href="{{ route('admin.documents') }}"
                        wire:navigate
                        class="nav-link {{ request()->routeIs('admin.documents') ? 'active' : '' }}"
                    >
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="sidebar-text">Dokumen</span>
                    </a>
                </nav>
            </aside>

            <main class="admin-main">
                <nav class="navbar navbar-expand bg-body-tertiary border-bottom admin-topbar">
                    <div class="container-fluid">
                        <button id="sidebarToggle" class="btn btn-outline-primary">
                            <i class="bi bi-list"></i>
                        </button>

                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <button id="themeToggle" class="btn btn-outline-secondary" type="button" title="Toggle theme">
                                <i class="bi bi-sun" data-theme-icon></i>
                            </button>

                            <div class="dropdown">
                                <button
                                    class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <i class="bi bi-person-circle"></i>
                                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.preventDefault();">
                                            <i class="bi bi-person me-2"></i>Profile
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                            <i class="bi bi-box-arrow-right"></i>
                                            <span>Logout</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="container-fluid py-4">
                    {{ $slot }}
                </div>

                <footer class="admin-footer py-3 border-top bg-body">
                    <div class="container text-center small text-muted">
                        © 2026 LENTERA - Politeknik Negeri Samarinda. All Rights Reserved.
                    </div>
                </footer>
            </main>
        </div>

        <x-confirmation-modal id="logoutModal" title="Keluar dari LENTERA" type="danger">
            Apakah Anda yakin ingin logout saat ini ?
            <x-slot:footer>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4">Logout</button>
                </form>
            </x-slot:footer>
        </x-confirmation-modal>

        @livewireScripts
    </body>
</html>

