<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/calculate', [HomeController::class, 'calculate'])->name('calculator.calculate');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');
Route::get('/kvkk', [PageController::class, 'kvkk'])->name('kvkk');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::post('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ApplicationController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])
        ->name('applications.show');
});

Route::middleware(['auth', 'can:manage-applications'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/applications/{application}/approve', [DashboardController::class, 'approve'])
        ->name('applications.approve');
    Route::patch('/applications/{application}/reject', [DashboardController::class, 'reject'])
        ->name('applications.reject');
});
