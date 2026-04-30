<?php

use App\Livewire\Auth\Login;
use App\Livewire\Admin\Document as AdminDocument;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\User as AdminUser;
use App\Livewire\Settings\Profile;
use App\Livewire\User\Chatbot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Route::get('/login', Login::class)->name('login');
// Route::get('/activate', Activate::class)->name('activate');

Route::middleware(['auth', 'ensure.active'])->group(function () {

    Route::get('/settings/profile', Profile::class)->name('settings.profile');
    
    Route::middleware(['is.role:admin'])->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/admin/pengguna', AdminUser::class)->name('admin.users');
        Route::get('/admin/dokumen', AdminDocument::class)->name('admin.documents');
    });

    Route::middleware(['is.role:mahasiswa,dosen,staff'])->group(function () {
        Route::get('/chatbot/{slug?}', Chatbot::class)->name('chatbot');
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
    // Route::get('/activate', Activate::class)->name('activate');
});