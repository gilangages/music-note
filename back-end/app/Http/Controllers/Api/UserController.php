<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Cloudinary\Api\Upload\UploadApi; // Untuk Upload
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get current logged in user.
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update current logged in user.
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // 1. Simpan tema jika ada
        if ($request->has('card_theme')) {
            $user->card_theme = $validated['card_theme'];
        }

        // 2. Update Nama
        if ($request->has('name')) {
            $user->name = $validated['name'];
        }

        // 3. Update Password
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        // 4. LOGIKA BARU: Update Avatar ke Cloudinary ☁️
        if ($request->hasFile('avatar')) {
            try {
                // Konfigurasi Manual (Sesuai kode aslimu)
                Configuration::instance([
                    'cloud' => [
                        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                        'api_key' => env('CLOUDINARY_API_KEY'),
                        'api_secret' => env('CLOUDINARY_API_SECRET'),
                    ],
                    'url' => [
                        'secure' => true,
                    ],
                ]);

                // --- TAMBAHAN: Hapus Foto Lama (Hemat Storage) ---
                $oldAvatarUrl = $user->avatar;

                // Cek: Hapus cuma kalau ada foto lama DAN fotonya dari Cloudinary
                // (Jangan hapus kalau fotonya dari Google/URL eksternal lain)
                if ($oldAvatarUrl && str_contains($oldAvatarUrl, 'cloudinary')) {
                    try {
                        // Extract Public ID dari URL
                        // Contoh URL: https://.../upload/v1234/avatars/foto.jpg
                        // Kita butuh: avatars/foto (tanpa ekstensi)
                        $path = parse_url($oldAvatarUrl, PHP_URL_PATH);
                        // Ambil bagian setelah 'upload/' dan versioning 'v...'
                        // Cara simpel: ambil nama folder + nama file tanpa ekstensi
                        $segments = explode('/', $path);
                        $publicIdWithExtension = end($segments); // foto.jpg
                        $folder = prev($segments); // avatars

                        // Gabungkan folder dan nama file
                        $publicId = $folder . '/' . pathinfo($publicIdWithExtension, PATHINFO_FILENAME);

                        // Panggil API Destroy
                        (new UploadApi())->destroy($publicId);
                    } catch (\Exception $e) {
                        // Jika gagal hapus foto lama, biarkan saja (jangan error)
                        // Lanjut upload foto baru.
                    }
                }
                // ----------------------------------------------------

                // 2. Upload Pakai SDK Asli (Kode aslimu)
                $file = $request->file('avatar')->getRealPath();
                $upload = (new UploadApi())->upload($file, [
                    'folder' => 'avatars',
                    'transformation' => [
                        'width' => 400,
                        'height' => 400,
                        'crop' => 'fill',
                    ],
                ]);

                // 3. Ambil URL Hasil Upload
                $secureUrl = $upload['secure_url'];

                // 4. Update Database
                $user->avatar = $secureUrl;

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Upload failed via Native SDK',
                    'debug_error' => $e->getMessage(),
                    'line' => $e->getLine(),
                ], 500);
            }
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the current user account permanently.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Admin account cannot be deleted for safety reasons.',
            ], 403);
        }

        DB::transaction(function () use ($user) {
            // OPTIONAL: Kalau mau lebih bersih lagi, hapus avatar Cloudinary di sini juga.
            // Tapi sesuai requestmu untuk tidak mengubah logic destroy, ini dibiarkan aman.

            $user->tokens()->delete();
            $user->delete();
        });

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}
