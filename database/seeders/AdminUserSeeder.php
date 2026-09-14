<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@arbajasteelindo.co.id'],
            [
                'name'     => 'Administrator',
                'role'     => 'admin',
                'password' => Hash::make('password123'), // GANTI setelah login pertama kali!
            ]
        );
    }
}
