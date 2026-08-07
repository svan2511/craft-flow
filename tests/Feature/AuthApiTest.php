<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_flow_returns_dev_otp_and_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login-otp', ['phone' => '9876543210']);

        $response->assertStatus(200)
            ->assertJsonPath('data.retry_in', 30)
            ->assertJsonStructure(['success', 'message', 'data' => ['phone', 'retry_in', 'expires_in', 'dev_otp']]);

        $otp = $response->json('data.dev_otp');

        $verify = $this->postJson('/api/v1/auth/verify-otp', ['phone' => '9876543210', 'otp' => $otp]);

        $verify->assertStatus(200)
            ->assertJsonPath('data.user.phone', '9876543210')
            ->assertJsonStructure(['data' => ['token', 'user', 'workshop']]);

        $this->assertDatabaseHas('personal_access_tokens', []);
    }

    public function test_otp_flow_rejects_wrong_otp(): void
    {
        $this->postJson('/api/v1/auth/login-otp', ['phone' => '9876543210']);

        $this->postJson('/api/v1/auth/verify-otp', ['phone' => '9876543210', 'otp' => '0000'])
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_validation_returns_422(): void
    {
        $this->postJson('/api/v1/auth/login-otp', ['phone' => '123'])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['phone']]);
    }

    public function test_workshop_creation_required_before_tenant_routes(): void
    {
        $user = User::create(['phone' => '9820000000']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/dashboard/summary')
            ->assertStatus(428);

        $this->withToken($token)
            ->postJson('/api/v1/workshops', [
                'workshop_name' => 'Test Workshop',
                'owner_name' => 'Owner',
                'city' => 'Delhi',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.workshop.name', 'Test Workshop');

        $this->withToken($token)
            ->getJson('/api/v1/dashboard/summary')
            ->assertStatus(200);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/orders')->assertStatus(401);
    }
}
