<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Login con cuenta "creada" debe fallar
     */
    public function test_login_fails_with_cuenta_creada(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'cuenta' => 'creada',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Tu cuenta existe, pero aun no esta activa, contacta a tu proveedor para consultar los detalles.',
            ]);
    }

    /**
     * Test: Login con cuenta "activada" debe ser exitoso
     */
    public function test_login_successful_with_cuenta_activada(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'cuenta' => 'activada',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    /**
     * Test: Login con cuenta "suspendida" debe fallar y mostrar razón
     */
    public function test_login_fails_with_cuenta_suspendida(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'cuenta' => 'suspendida',
            'razon_suspendida' => 'Falta de pago',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Tu cuenta ha sido suspendida. Razón: Falta de pago',
            ]);
    }

    /**
     * Test: Login con cuenta "suspendida" sin razón especificada
     */
    public function test_login_fails_with_cuenta_suspendida_no_reason(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'cuenta' => 'suspendida',
            'razon_suspendida' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Tu cuenta ha sido suspendida. Razón: No especificada',
            ]);
    }

    /**
     * Test: Login con cuenta "cancelada" debe fallar y mostrar razón
     */
    public function test_login_fails_with_cuenta_cancelada(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'cuenta' => 'cancelada',
            'razon_suspendida' => 'Violación de términos de servicio',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Tu cuenta ha sido cancelada. Razón: Violación de términos de servicio',
            ]);
    }

    /**
     * Test: Login con cuenta "cancelada" sin razón especificada
     */
    public function test_login_fails_with_cuenta_cancelada_no_reason(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'cuenta' => 'cancelada',
            'razon_suspendida' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Tu cuenta ha sido cancelada. Razón: No especificada',
            ]);
    }
}
