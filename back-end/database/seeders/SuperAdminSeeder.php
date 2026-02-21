<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $email = config('services.admin.email');
        $password = config('services.admin.password');

        if (!$email || !$password) {
            $this->command->error('Please filll ADMIN_EMAIL and ADMIN_PASS in file .env!');
            return;
        }

        // Buat Akun Super Admin
        $user = User::firstOrCreate(
            ['email' => $email], // Cek berdasarkan email agar tidak duplikat
            [
                'name' => 'Super Admin Abdian',
                'password' => Hash::make($password), // Ganti dengan password yang aman
                'role' => 'admin',
                'avatar' => null,
                'email_verified_at' => now(),
            ]
        );

        // Opsi: Tambahkan beberapa user dummy untuk testing moderasi
        // User::factory(10)->create();

        $this->command->info("Admin Ready: $email");
    }
}
