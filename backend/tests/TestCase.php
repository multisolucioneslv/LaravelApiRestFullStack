<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar seeders necesarios para tests
        $this->seed(\Database\Seeders\PermissionsSeeder::class);

        // Crear datos base para foreign keys
        $this->createBasicData();
    }

    /**
     * Crear datos básicos necesarios para los tests
     */
    protected function createBasicData(): void
    {
        // Crear géneros (modelo en inglés: Gender)
        \App\Models\Gender::create(['id' => 1, 'sexo' => 'Masculino', 'inicial' => 'M']);
        \App\Models\Gender::create(['id' => 2, 'sexo' => 'Femenino', 'inicial' => 'F']);
        \App\Models\Gender::create(['id' => 3, 'sexo' => 'Otro', 'inicial' => 'O']);

        // Crear teléfono y moneda base
        $telefono = \App\Models\Phone::create(['telefono' => '(702)337-9581']);
        $currency = \App\Models\Currency::create([
            'codigo' => 'USD',
            'nombre' => 'Dólar Estadounidense',
            'simbolo' => '$',
            'tasa_cambio' => 1.0000,
            'activo' => true,
        ]);

        // Crear empresa base
        \App\Models\Empresa::create([
            'id' => 1,
            'nombre' => 'Empresa Test',
            'telefono_id' => $telefono->id,
            'currency_id' => $currency->id,
            'email' => 'test@empresa.com',
            'direccion' => 'Dirección Test',
            'zona_horaria' => 'America/Los_Angeles',
            'horarios' => [],
            'activo' => true,
        ]);
    }

    /**
     * Helper para crear un usuario de prueba con rol específico
     */
    protected function createUser(string $role = 'Usuario', array $attributes = []): \App\Models\User
    {
        $user = \App\Models\User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    /**
     * Helper para autenticar un usuario y obtener su token JWT
     */
    protected function actingAsUser(\App\Models\User $user): string
    {
        return \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
    }

    /**
     * Helper para hacer request autenticado
     */
    protected function authenticatedJson(string $method, string $uri, array $data = [], \App\Models\User $user = null)
    {
        if (!$user) {
            $user = $this->createUser('SuperAdmin');
        }

        $token = $this->actingAsUser($user);

        return $this->json($method, $uri, $data, [
            'Authorization' => "Bearer {$token}"
        ]);
    }
}
