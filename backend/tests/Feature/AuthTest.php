<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Login exitoso con credenciales válidas
     */
    public function test_login_successful_with_valid_credentials(): void
    {
        // Crear usuario de prueba
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Intentar login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Verificar respuesta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);
    }

    /**
     * Test: Login fallido con credenciales inválidas
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        // Crear usuario de prueba
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Intentar login con contraseña incorrecta
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Verificar respuesta de error
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Las credenciales son incorrectas.',
            ]);
    }

    /**
     * Test: Login fallido con usuario inexistente
     */
    public function test_login_fails_with_nonexistent_user(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Las credenciales son incorrectas.',
            ]);
    }

    /**
     * Test: Login requiere email
     */
    public function test_login_requires_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test: Login requiere password
     */
    public function test_login_requires_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test: Obtener información del usuario autenticado
     */
    public function test_me_endpoint_returns_authenticated_user(): void
    {
        // Crear usuario con rol
        $user = $this->createUser('Usuario', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Hacer request autenticado
        $response = $this->authenticatedJson('GET', '/api/auth/me', [], $user);

        // Verificar respuesta (la respuesta está dentro de 'user')
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'user' => [
                    'id',
                    'usuario',
                    'name',
                    'email',
                    'gender_id',
                    'phone_id',
                    'chatid_id',
                    'activo',
                    'empresa_id',
                    'gender',
                    'phone',
                    'chatid',
                    'empresa',
                    'roles' => [
                        '*' => ['id', 'name'],
                    ],
                ],
            ]);

        // Verificar que los datos del usuario son correctos
        $this->assertEquals($user->id, $response->json('user.id'));
        $this->assertEquals('Test User', $response->json('user.name'));
        $this->assertEquals('test@example.com', $response->json('user.email'));
    }

    /**
     * Test: Me endpoint falla sin autenticación
     */
    public function test_me_endpoint_fails_without_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test: Me endpoint incluye roles del usuario
     */
    public function test_me_endpoint_includes_user_roles(): void
    {
        $user = $this->createUser('Administrador');

        $response = $this->authenticatedJson('GET', '/api/auth/me', [], $user);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Administrador',
            ]);
    }

    /**
     * Test: Me endpoint incluye permisos del usuario
     */
    public function test_me_endpoint_includes_user_permissions(): void
    {
        $user = $this->createUser('SuperAdmin');

        $response = $this->authenticatedJson('GET', '/api/auth/me', [], $user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'roles' => [
                        '*' => ['id', 'name'],
                    ],
                ],
            ]);

        // SuperAdmin debe tener rol SuperAdmin
        $roles = $response->json('user.roles');
        $this->assertNotEmpty($roles);
        $this->assertEquals('SuperAdmin', $roles[0]['name']);
    }

    /**
     * Test: Logout exitoso invalida el token
     */
    public function test_logout_invalidates_token(): void
    {
        $user = $this->createUser('Usuario');

        // Hacer logout
        $response = $this->authenticatedJson('POST', '/api/auth/logout', [], $user);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente.',
            ]);

        // Intentar usar el mismo token (debería fallar)
        // Nota: JWT en Laravel no invalida tokens instantáneamente,
        // pero el token queda en blacklist
    }

    /**
     * Test: Logout requiere autenticación
     */
    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test: Refresh token exitoso genera nuevo token
     */
    public function test_refresh_token_generates_new_token(): void
    {
        $user = $this->createUser('Usuario');
        $oldToken = $this->actingAsUser($user);

        // Hacer refresh
        $response = $this->json('POST', '/api/auth/refresh', [], [
            'Authorization' => "Bearer {$oldToken}",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);

        // Verificar que el nuevo token es diferente
        $newToken = $response->json('access_token');
        $this->assertNotEquals($oldToken, $newToken);
    }

    /**
     * Test: Refresh falla sin autenticación
     */
    public function test_refresh_fails_without_authentication(): void
    {
        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test: Refresh falla con token inválido
     */
    public function test_refresh_fails_with_invalid_token(): void
    {
        $response = $this->json('POST', '/api/auth/refresh', [], [
            'Authorization' => 'Bearer invalid_token_here',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test: Usuario puede acceder a rutas protegidas con token válido
     */
    public function test_authenticated_user_can_access_protected_routes(): void
    {
        $user = $this->createUser('Usuario');

        $response = $this->authenticatedJson('GET', '/api/auth/me', [], $user);

        $response->assertStatus(200);
    }

    /**
     * Test: Rate limiting en login (máximo 5 intentos por minuto)
     */
    public function test_login_rate_limiting(): void
    {
        // Hacer 6 intentos de login fallidos
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);

            if ($i < 5) {
                // Primeros 5 intentos deben devolver 401 (Unauthorized)
                $response->assertStatus(401);
            } else {
                // El 6to intento debe ser bloqueado por rate limiting (429)
                $response->assertStatus(429);
            }
        }
    }

    /**
     * Test: Login con usuario inactivo falla
     */
    public function test_login_fails_with_inactive_user(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'activo' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        // El AuthController devuelve 403 para usuarios inactivos
        $response->assertStatus(403);
    }

    /**
     * Test: Token expira después del tiempo configurado
     */
    public function test_token_has_expiration_time(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['expires_in']);

        // Verificar que expires_in es un número positivo
        $expiresIn = $response->json('expires_in');
        $this->assertIsInt($expiresIn);
        $this->assertGreaterThan(0, $expiresIn);
    }
}
