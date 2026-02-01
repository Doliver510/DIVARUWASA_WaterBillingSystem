<?php

namespace Tests\Feature\Auth;

use App\Models\Consumer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_with_email(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_consumer_can_authenticate_with_account_number(): void
    {
        // Create consumer role
        $consumerRole = Role::firstOrCreate(
            ['slug' => 'consumer'],
            ['name' => 'Consumer', 'slug' => 'consumer']
        );

        // Create block for consumer
        $block = \App\Models\Block::firstOrCreate(
            ['name' => 'Block 1'],
            ['name' => 'Block 1', 'sort_order' => 1]
        );

        // Create user without email
        $user = User::factory()->create([
            'role_id' => $consumerRole->id,
            'email' => null,
            'password' => Hash::make('TestPassword@001'),
        ]);

        // Create consumer with account number (manually, no factory)
        Consumer::create([
            'user_id' => $user->id,
            'id_no' => '001',
            'block_id' => $block->id,
            'lot_number' => 1,
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => '001',
            'password' => 'TestPassword@001',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_account_number(): void
    {
        $this->post('/login', [
            'login' => '999',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
