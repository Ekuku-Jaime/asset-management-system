<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\SetPasswordMail;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showRegistrationForm($token = null)
    {
        if (!$token) {
            abort(404);
        }

        $user = User::where('activation_token', $token)
            ->where('active', false)
            ->firstOrFail();

        return view('auth.set-password', compact('token', 'user'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('activation_token', $request->token)
            ->where('active', false)
            ->firstOrFail();

        $user->update([
            'password' => Hash::make($request->password),
            'active' => true,
            'activation_token' => null,
            'email_verified_at' => now(),
        ]);

        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Password set successfully! Welcome to Asset Management System.');
    }
}