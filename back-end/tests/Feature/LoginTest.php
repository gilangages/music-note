<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// Biar warning "Metadata" hilang

class LoginTest extends TestCase
{
    use RefreshDatabase; // Wajib: agar database di-reset setiap test jalan

    #[Test]
    public function user_can_login_returns_indonesian_message()
    {
        // 1. KITA HARUS BUAT USERNYA DULU DI DATABASE
        // Karena database testing itu kosong melompong
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Tembak API Login (Perhatikan URL-nya: /api/users/login)
        $response = $this->withHeaders([
            'Accept-Language' => 'id', // Request Bahasa Indo
        ])->postJson('/api/users/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // 3. Cek Hasil
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login berhasil', // Pastikan ini sama persis dengan lang/id/messages.php
            ]);
    }

    #[Test]
    public function user_login_validation_error_english()
    {
        // Test Validasi (Form Kosong)
        $response = $this->withHeaders([
            'Accept-Language' => 'en', // Request Bahasa Inggris
        ])->postJson('/api/users/login', []); // Kirim body kosong

        $response->assertStatus(422) // 422 Unprocessable Entity (Error Validasi)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
