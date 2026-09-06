<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin account
        User::firstOrCreate(
            ['email' => 'admin@platinumproject.my.id'],
            [
                'name'     => 'Admin Platinum Project',
                'email'    => 'admin@platinumproject.my.id',
                'phone'    => '085700751642',
                'password' => Hash::make('PlatinumAdmin@2026'),
                'role'     => 'admin',
            ]
        );

        $this->command->info('✅ Admin account seeded!');
        $this->command->warn('⚠️  Default password: PlatinumAdmin@2026 — ganti segera di production!');
    }
}
