<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;

// Public Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Publicly Accessible Ticket Detail Route (Read-Only/Simplified for Guests)
Route::get('/tickets/{ticket}', [TicketController::class, 'show']);

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [TicketController::class, 'index']);
    Route::get('/users', [AuthController::class, 'userList'])->name('users.index');
    Route::get('/tickets', [TicketController::class, 'ticketList'])->name('tickets.index');


    Route::prefix('api')->group(function () {
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::patch('/tickets/{ticket}', [TicketController::class, 'update']);
        Route::match(['patch', 'post'], '/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
        Route::post('/tickets/{ticket}/rollback', [TicketController::class, 'rollback']);
        Route::post('/tickets/{ticket}/note', [TicketController::class, 'addNote']);
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);

        Route::post('/users', [AuthController::class, 'store']);
        Route::patch('/users/{user}', [AuthController::class, 'update']);
        Route::delete('/users/{user}', [AuthController::class, 'destroy']);


    });
});

