<?php

namespace Tests\Unit\Models;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->assertTrue($user->active);
    }

    public function test_user_is_admin_returns_false_for_regular_user(): void
    {
        $user = User::factory()->create(['type' => UserType::USER]);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isUser());
    }

    public function test_user_is_admin_returns_true_for_admin(): void
    {
        $user = User::factory()->create(['type' => UserType::ADMIN]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isUser());
    }

    public function test_user_is_active_returns_correct_value(): void
    {
        $activeUser = User::factory()->create(['active' => true]);
        $inactiveUser = User::factory()->create(['active' => false]);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
    }

    public function test_user_has_profiles_relationship(): void
    {
        $user = User::factory()->create();

        $this->assertCount(0, $user->profiles);
    }

    public function test_user_attributes_are_mass_assignable(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '555-0100',
        ]);

        $this->assertEquals('Jane Doe', $user->name);
        $this->assertEquals('Jane', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('555-0100', $user->phone);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->assertNotEquals('secret123', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secret123', $user->password));
    }

    public function test_user_has_soft_deletes(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted($user);
    }

    public function test_user_has_api_tokens(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotEmpty($token->plainTextToken);
        $this->assertEquals('test-token', $token->accessToken->name);
    }
}
