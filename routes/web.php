<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
});
