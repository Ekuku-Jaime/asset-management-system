<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\SetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }
    
    public function data()
    {
        $users = User::select(['id', 'name', 'email', 'role', 'active', 'created_at']);
        
        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('actions', function($user) {
                return '';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:admin,manager,user',
        ]);
        
        try {
            $token = Str::random(60);
            
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'active' => false,
                'activation_token' => $token,
            ]);
            
            Mail::to($user->email)->send(new SetPasswordMail($user, $token));
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully. Invitation email sent.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function resend(User $user)
    {
        try {
            if ($user->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already active.'
                ], 400);
            }
            
            $token = Str::random(60);
            $user->update(['activation_token' => $token]);
            
            Mail::to($user->email)->send(new SetPasswordMail($user, $token));
            
            return response()->json([
                'success' => true,
                'message' => 'Invitation email resent successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy(User $user)
    {
        try {
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 400);
            }
            
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }
}