<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function createLogin()
    {
        return view('pages.auth.login');
    }

    public function storeLogin(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.'
            ])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        Auth::logout();
        
        return redirect()->route('login');
    }

    public function edit()
    {
        return view('pages.auth.edit-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        if (Hash::check($request->current_password, Auth::user()->password)) {
            Auth::user()->update([
                'password' => Hash::make($request->password)
            ]);
            return back()->with('success', 'Your password has been updated!');
        }

        throw ValidationException::withMessages([
            'current_password' => 'Your current password does not match with our record',
        ]);
    }
}
