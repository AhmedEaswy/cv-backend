<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TemplateSeeder::class,
            CoverLetterTemplateSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(LocalFakeDataSeeder::class);
        }
    }
}
