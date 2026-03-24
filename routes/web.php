<?php

use App\Livewire\Auth\Activate;
use App\Livewire\Auth\Login;
use App\Livewire\Admin\Documents\Index as AdminDocumentsIndex;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Livewire\Admin\Dashboard\Index as DashboardIndex;
use App\Livewire\Settings\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Route::get('/login', Login::class)->name('login');
// Route::get('/activate', Activate::class)->name('activate');

Route::middleware(['auth', 'ensure.active'])->group(function () {

    Route::get('/settings/profile', Profile::class)->name('settings.profile');
    
    Route::middleware(['is.role:admin'])->group(function () {
        Route::get('/dashboard', DashboardIndex::class)->name('admin.dashboard');
        Route::get('/admin/pengguna', AdminUsersIndex::class)->name('admin.users');
        Route::get('/admin/dokumen', AdminDocumentsIndex::class)->name('admin.documents');
    });

    Route::middleware(['is.role:mahasiswa,dosen,staff'])->group(function () {
        // Route::get('/chat', ChatIndex::class)->name('chat');
    });

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