<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login endpoint untuk siswa
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        // Validate credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

        // Only siswa can login via API
        if ($user->role !== 'siswa') {
            throw ValidationException::withMessages([
                'email' => ['Hanya siswa yang dapat login via API.'],
            ]);
        }

        // Get student data
        $student = $user->student;

        // Generate token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'student' => $student ? [
                    'id' => $student->id,
                    'nisn' => $student->nisn,
                    'nama' => $student->nama,
                    'kelas' => $student->kelas,
                    'qr_token' => $student->qr_token,
                ] : null,
            ],
        ], 200);
    }

    /**
     * Logout endpoint - revoke token
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ], 200);
    }

    /**
     * Get current user with student profile
     * GET /api/user
     */
    public function getUser(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'student' => $student ? [
                    'id' => $student->id,
                    'nisn' => $student->nisn,
                    'nama' => $student->nama,
                    'kelas' => $student->kelas,
                    'qr_token' => $student->qr_token,
                ] : null,
            ],
        ], 200);
    }
}
