<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;

// Public Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Publicly Accessible Ticket Detail Route (Read-Only/Simplified for Guests)
Route::get('/tickets/{ticket}', [TicketController::class, 'show']);

// Protected Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [TicketController::class, 'index']);

    Route::prefix('api')->group(function () {
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
    });
});
