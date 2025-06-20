<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat user admin default
        User::create([
            'name' => 'Admin Angkringan',
            'email' => 'admin@angkringan.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'angkringan_name' => 'Angkringan Pak Budi',
            'address' => 'Jl. Malioboro No. 123, Yogyakarta',
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Membuat user admin biasa
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@angkringan.com',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
            'angkringan_name' => 'Angkringan Bu Sari',
            'address' => 'Jl. Tugu No. 45, Yogyakarta',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}