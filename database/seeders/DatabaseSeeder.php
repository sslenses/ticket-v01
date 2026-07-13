<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Akun utama admin untuk login pasca migrate:fresh
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Main Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );
    }
}
