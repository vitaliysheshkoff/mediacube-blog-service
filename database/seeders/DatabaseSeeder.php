<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'test@example.com',
                'password' => 'password',
                'role' => 'admin',
            ]);
        }

    }
}
