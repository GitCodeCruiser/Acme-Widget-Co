<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's admin user.
     */
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@admin.com']);

        $admin->forceFill([
            'name' => 'Admin',
            'password' => '12345678',
            'is_admin' => true,
        ]);

        $admin->save();
    }
}