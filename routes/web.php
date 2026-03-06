<?php

use App\Livewire\Auth\Activate;
use App\Livewire\Auth\Login;
use App\Livewire\Admin\Documents\Index as AdminDocumentsIndex;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Route::get('/login', Login::class)->name('login');
// Route::get('/activate', Activate::class)->name('activate');

Route::middleware(['auth', 'ensure.active'])->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    Route::get('/admin/pengguna', AdminUsersIndex::class)->name('admin.users');
    Route::get('/admin/dokumen', AdminDocumentsIndex::class)->name('admin.documents');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/activate', Activate::class)->name('activate');
});