<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@wagateway.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Test Tenant User
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Demo Tenant User
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@wagateway.com',
            'password' => Hash::make('demo123'),
            'email_verified_at' => now(),
        ]);
    }
}
