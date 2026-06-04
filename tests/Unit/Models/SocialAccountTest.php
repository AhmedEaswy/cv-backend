<?php

namespace Tests\Unit\Models;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_account_can_be_created(): void
    {
        $user = User::factory()->create();

        $account = SocialAccount::create([
            'user_id' => $user->id,
            'provider_name' => 'google',
            'provider_id' => '123456789',
            'provider_token' => 'test-token',
        ]);

        $this->assertDatabaseHas('social_accounts', ['provider_name' => 'google']);
        $this->assertEquals($user->id, $account->user_id);
    }

    public function test_social_account_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $account = SocialAccount::create([
            'user_id' => $user->id,
            'provider_name' => 'linkedin',
            'provider_id' => '987654321',
        ]);

        $this->assertEquals($user->id, $account->user->id);
    }
}
