<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,industry,school',
        ];

        if ($request->role === 'school') {
            $rules['npsn'] = 'required|string';
            $rules['school_name'] = 'required|string';
        }

        if ($request->role === 'student') {
            $rules['school_name'] = 'required|string';
            $rules['department'] = 'required|string';
        }

        if ($request->role === 'industry') {
            $rules['nib'] = 'required|string';
            $rules['company_name'] = 'required|string';
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Create profile based on role
        $profileData = [];
        if ($request->role === 'school') {
            $profileData['npsn'] = $validated['npsn'];
            $profileData['school'] = $validated['school_name']; // maps to school field
        } elseif ($request->role === 'industry') {
            $profileData['nib'] = $validated['nib'];
            $profileData['company_name'] = $validated['company_name'];
        } elseif ($request->role === 'student') {
            $profileData['school'] = $validated['school_name'];
            $profileData['department'] = $validated['department'];
        }

        $user->profile()->create($profileData);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('profile'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = User::with('profile')->where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
