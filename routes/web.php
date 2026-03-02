<?php

use App\Livewire\Auth\Activate;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', Login::class)->name('login');
Route::get('/activate', Activate::class)->name('activate');

Route::middleware(['auth', 'ensure.active'])->group(function () {
    Route::get('/dashboard', function () {
        return view('welcome');
    })->name('dashboard');
});

// Route::middleware(['guest'])->group(function () {
//     Route::get('/login', Login::class)->name('login');
//     Route::get('/activate', Activate::class)->name('activate');
// });