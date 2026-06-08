<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Models\User;
use App\Models\Otp;
use App\Jobs\SendOtpEmailJob;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AuthController extends Controller
{
    // REGISTER - kirim OTP
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'role' => 'peserta',
        ]);

        $this->sendOtp($user);

        return response()->json([
            'message' => 'User registered. Please verify OTP sent to your email.',
            'user_id' => $user->id
        ], 201);
    }

    // LOGIN - langsung beri token tanpa OTP
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Hapus token lama
        $user->tokens()->delete();

$token = $user->createToken('auth_token', ['*'])->plainTextToken;

$cookie = cookie(
    'auth_token',
    $token,
    60 * 24,      // 1 hari
    '/',
    null,         // domain (null = current domain)
    false,        // secure (false untuk localhost)
    false,        // httpOnly (false agar bisa dibaca JS)
    false,
    'lax'
);

return response()->json([
    'message' => 'Login successful',
    'user' => $user->only(['id', 'name', 'email', 'role']),
    'token' => $token,
])->withCookie($cookie);
    }

    // Request ulang OTP (untuk registrasi yang belum verifikasi)
    public function requestOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $this->sendOtp($user);
        return response()->json(['message' => 'OTP sent to your email']);
    }

    // Verifikasi OTP (untuk registrasi)
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $otp = Otp::where('user_id', $request->user_id)
                  ->where('code', $request->code)
                  ->where('is_used', false)
                  ->where('expires_at', '>', Carbon::now())
                  ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 401);
        }

        $otp->update(['is_used' => true]);

        $user = User::find($request->user_id);
        // Hapus token lama jika ada, lalu buat token baru
        $user->tokens()->delete();
       // Di method login() dan verifyOtp()
$token = $user->createToken('auth_token', ['*'])->plainTextToken;

$cookie = cookie(
    'auth_token',
    $token,
    60 * 24,      // 1 hari
    '/',
    null,         // domain (null = current domain)
    false,        // secure (false untuk localhost)
    false,        // httpOnly (false agar bisa dibaca JS)
    false,
    'lax'
);

return response()->json([
    'message' => 'Login successful',
    'user' => $user->only(['id', 'name', 'email', 'role']),
    'token' => $token,
])->withCookie($cookie);
    }
    public function logout()
    {
        $user = auth()->user();
        if ($user) {
            $user->tokens()->delete();
        }
        $cookie = cookie()->forget('auth_token');
        return response()->json(['message' => 'Logged out'])->withCookie($cookie);
    }

    public function me()
    {
        return response()->json(auth()->user()->only(['id', 'name', 'email', 'role']));
    }

    private function sendOtp($user)
    {
        // Invalidate OTP lama
        Otp::where('user_id', $user->id)->update(['is_used' => true]);

        $code = sprintf("%06d", mt_rand(1, 999999));
        Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
            'is_used' => false,
        ]);

        SendOtpEmailJob::dispatch($user, $code);
    }
}