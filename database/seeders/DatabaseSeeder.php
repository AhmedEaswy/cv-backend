<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@app.com',
            'phone' => '+1234567890',
            'type' => UserType::ADMIN,
            'password' => bcrypt('123456789'),
        ]);

        User::factory()->create([
            'name' => 'Ahmed',
            'first_name' => 'Ahmed',
            'last_name' => 'Easwy',
            'email' => 'user@app.com',
            'phone' => '+1234567890',
            'type' => UserType::USER,
            'password' => bcrypt('123456789'),
        ]);

        $this->call([
            TemplateSeeder::class,
            ProfileSeeder::class,
        ]);
    }
}
