<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin account
        User::create([
            'identifier' => 'ADMIN001',
            'name' => 'System Administrator',
            'email' => 'admin@uniattend.edu',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->command->info('Default admin created: ADMIN001 / admin123');
    }
}
