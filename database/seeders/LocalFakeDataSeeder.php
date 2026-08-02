<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalFakeDataSeeder extends Seeder
{
    /**
     * Demo users, profiles, and cover letters for local development only.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->warn('LocalFakeDataSeeder skipped (APP_ENV is not local).');

            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@app.com'],
            [
                'name' => 'Admin User',
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'phone' => '+1234567890',
                'type' => UserType::ADMIN,
                'password' => Hash::make('123456789'),
            ],
        );

        User::firstOrCreate(
            ['email' => 'user@app.com'],
            [
                'name' => 'Ahmed',
                'first_name' => 'Ahmed',
                'last_name' => 'Easwy',
                'phone' => '+1234567890',
                'type' => UserType::USER,
                'password' => Hash::make('123456789'),
            ],
        );

        $this->call([
            ProfileSeeder::class,
            CoverLetterSeeder::class,
            PublicProfileSeeder::class,
        ]);
    }
}
