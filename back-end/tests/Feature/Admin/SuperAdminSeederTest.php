<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminSeederTest extends TestCase
{
    use RefreshDatabase; // Memastikan database di-reset setiap test

    public function test_super_admin_seeder_berhasil_membuat_admin()
    {
        // 1. Setup (Mocking konfigurasi .env)
        Config::set('services.admin.email', 'admin@resonate.test');
        Config::set('services.admin.password', 'rahasia123');

        // 2. Action (Jalankan seeder)
        $this->seed(SuperAdminSeeder::class);

        // 3. Assert (Pastikan data masuk ke database)
        $this->assertDatabaseHas('users', [
            'email' => 'admin@resonate.test',
            'name' => 'Super Admin Abdian',
            'role' => 'admin',
        ]);

        // Pastikan password ter-hash dengan benar
        $user = User::where('email', 'admin@resonate.test')->first();
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_super_admin_seeder_tidak_jalan_jika_env_kosong()
    {
        // 1. Setup (Kosongkan password)
        Config::set('services.admin.email', 'admin@resonate.test');
        Config::set('services.admin.password', null);

        // 2. Action (Jalankan seeder)
        $this->seed(SuperAdminSeeder::class);

        // 3. Assert (Pastikan user TIDAK terbuat di database)
        $this->assertDatabaseMissing('users', [
            'email' => 'admin@resonate.test',
        ]);
    }
    public function test_super_admin_seeder_tidak_duplikat_jika_dijalankan_dua_kali()
    {
        // 1. Setup (Mocking konfigurasi .env)
        Config::set('services.admin.email', 'admin@resonate.test');
        Config::set('services.admin.password', 'rahasia123');

        // 2. Action (Jalankan seeder DUA KALI berturut-turut)
        $this->seed(SuperAdminSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        // 3. Assert (Pastikan jumlah user di database HANYA ADA 1)
        // Jika firstOrCreate() gagal bekerja, test ini akan error karena user menjadi 2
        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@resonate.test',
            'name' => 'Super Admin Abdian',
        ]);
    }
}
