<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // 1. Jika user tidak ditemukan sama sekali
        if (!$user) {
            return response()->json(['message' => __('messages.email_not_found')], 401);
        }

        // 2. Jika user ditemukan, tapi password di database NULL (User Google belum buat password)
        if (is_null($user->password) && $user->google_id) {
            return response()->json([
                'message' => __('messages.account_google'),
            ], 422);
        }

        // 3. Jika user ada, tapi password salah
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => __('messages.password_wrong')], 401);
        }

        // 4. CEK STATUS BANNED (Implementation Baru)
        if ($user->is_banned) {
            return response()->json([
                'message' => __('messages.account_banned'), // Kode khusus untuk frontend
                'status' => 'banned',

                'reason' => $user->ban_reason ?? 'Pelanggaran Aturan.',
            ], 403);
        }

        // Jika lolos semua pengecekan di atas, lanjut buat token...
        // $user->tokens()->delete(); biar bisa login di HP dan Laptop secara bersamaan
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('messages.login_success'),
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user), // <--- Lebih simpel, photo_url otomatis masuk
        ]);
    }
}
