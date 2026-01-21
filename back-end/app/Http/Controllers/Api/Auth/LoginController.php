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

        // 2. CEK STATUS BANNED (Pindahkan ke atas agar user banned tidak perlu cek password dulu)
        if ($user->is_banned) {
            return response()->json([
                'message' => __('messages.account_banned'),
                'status' => 'banned',
                'reason' => $user->ban_reason ?? 'Pelanggaran Aturan.',
            ], 403);
        }

        // 3. Cek Password
        if (!Hash::check($request->password, $user->password)) {
            // BEST PRACTICE FIX:
            // Jika password salah, KITA CEK apakah dia user Google?
            // Kita tidak peduli password-nya NULL atau tidak (karena bisa saja random hash).
            // Jika dia punya google_id, kita sarankan login via Google.
            if ($user->google_id) {
                return response()->json([
                    'message' => __('messages.account_google'),
                ], 422); // Gunakan 422 Unprocessable Entity atau 401 Unauthorized
            }

            // Jika bukan user Google, berarti murni salah password
            return response()->json(['message' => __('messages.password_wrong')], 401);
        }

        // Jika lolos semua pengecekan di atas, lanjut buat token...
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('messages.login_success'),
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }
}
