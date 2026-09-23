<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public const EMAIL = 'admin@cvcreator.app';

    public const PASSWORD = 'admin@cvcreator.app@app';

    /**
     * Ensure the default super admin exists (local and production).
     * Never overwrites an existing account's password or profile.
     */
    public function run(): void
    {
        $user = User::withTrashed()->firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'type' => UserType::ADMIN,
                'active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make(self::PASSWORD),
            ],
        );

        $dirty = false;

        if ($user->trashed()) {
            $user->restore();
            $dirty = true;
        }

        if ($user->type !== UserType::ADMIN) {
            $user->type = UserType::ADMIN;
            $dirty = true;
        }

        if ($user->active !== true) {
            $user->active = true;
            $dirty = true;
        }

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
            $dirty = true;
        }

        if ($dirty) {
            $user->save();
        }

        if ($this->command) {
            $this->command->info(
                $user->wasRecentlyCreated
                    ? 'Super admin created: '.self::EMAIL
                    : 'Super admin already present: '.self::EMAIL
            );
        }
    }
}
