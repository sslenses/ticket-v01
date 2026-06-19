<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Store a newly created user in storage (Admin only).
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized. Only administrators can create users.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'string', 'in:admin,dest_manager,staff,user'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display a listing of registered users (Admin & Dest Manager only).
     */
    public function userList()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('dest_manager')) {
            abort(403, 'Unauthorized. Only administrators and destination managers can access the user list.');
        }

        $users = \App\Models\User::oldest()->get();
        return view('auth.users', compact('users'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, \App\Models\User $user)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('dest_manager')) {
            abort(403, 'Unauthorized');
        }

        // dest_manager cannot edit admin
        if (auth()->user()->hasRole('dest_manager') && $user->hasRole('admin')) {
            abort(403, 'Unauthorized. Destination managers cannot edit administrators.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
        ];

        if (auth()->user()->hasRole('admin')) {
            $rules['role'] = ['required', 'string', 'in:admin,dest_manager,staff,user'];
        }

        $data = $request->validate($rules);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (auth()->user()->hasRole('admin') && isset($data['role'])) {
            $user->role = $data['role'];
        }

        if (!empty($data['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        $user->save();

        return response()->json($user);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(\App\Models\User $user)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized. Only administrators can delete users.');
        }

        // Prevent admin from deleting themselves
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
