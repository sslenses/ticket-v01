<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);

    // Development & testing helper routes
    Route::get('/login-as/{role}', function ($role) {
        $validRoles = ['admin', 'dest_manager', 'staff', 'user'];
        if (!in_array($role, $validRoles)) {
            return response()->json(['message' => 'Invalid role. Valid roles are: admin, dest_manager, staff, user'], 400);
        }

        $user = \App\Models\User::firstOrCreate(
            ['email' => "{$role}@example.com"],
            [
                'name' => ucfirst(str_replace('_', ' ', $role)),
                'password' => bcrypt('password'),
                'role' => $role,
            ]
        );

        auth()->login($user);

        return response()->json([
            'message' => "Successfully logged in as {$role}",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    });

    Route::get('/logout', function () {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    });
});
