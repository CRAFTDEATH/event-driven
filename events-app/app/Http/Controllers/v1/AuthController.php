<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $user = $request->user();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            // If using Sanctum/personal access tokens, revoke the current access token
            try {
                $current = $request->user()->currentAccessToken();
                if ($current) {
                    $current->delete();
                }
            } catch (\Throwable $e) {
                // ignore if method not available or other issues
            }
        }

        return response()->json(['message' => 'Logged out']);
    }
}
