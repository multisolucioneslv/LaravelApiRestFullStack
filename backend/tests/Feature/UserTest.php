<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Listar usuarios con paginación
     */
    public function test_index_returns_paginated_users(): void
    {
        // Crear usuario autenticado con permiso
        $admin = $this->createUser('SuperAdmin');

        // Crear algunos usuarios de prueba
        User::factory()->count(5)->create();

        $response = $this->authenticatedJson('GET', '/api/users', [], $admin);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'usuario', 'name', 'email'],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        // Debe incluir los 5 usuarios creados + el admin
        $this->assertGreaterThanOrEqual(6, $response->json('meta.total'));
    }

    /**
     * Test: Búsqueda de usuarios funciona correctamente
     */
    public function test_index_search_filters_users(): void
    {
        $admin = $this->createUser('SuperAdmin');

        // Crear usuario con nombre específico
        User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);

        User::factory()->create([
            'name' => 'María González',
            'email' => 'maria@example.com',
        ]);

        // Buscar por "Juan"
        $response = $this->authenticatedJson('GET', '/api/users?search=Juan', [], $admin);

        $response->assertStatus(200);

        $users = collect($response->json('data'));
        $this->assertTrue($users->contains('name', 'Juan Pérez'));
        $this->assertFalse($users->contains('name', 'María González'));
    }

    /**
     * Test: Crear usuario exitosamente
     */
    public function test_store_creates_user_successfully(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $userData = [
            'usuario' => 'testuser',
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password123!',
            'sexo_id' => 1,
            'telefono' => '12345678',
            'chatid' => '987654321',
            'activo' => true,
            'roles' => ['Usuario'],
        ];

        $response = $this->authenticatedJson('POST', '/api/users', $userData, $admin);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'usuario', 'name', 'email'],
            ]);

        // Verificar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('users', [
            'usuario' => 'testuser',
            'email' => 'testuser@example.com',
        ]);
    }

    /**
     * Test: Crear usuario requiere autenticación
     */
    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/users', [
            'usuario' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test: Crear usuario valida campos requeridos
     */
    public function test_store_validates_required_fields(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $response = $this->authenticatedJson('POST', '/api/users', [], $admin);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['usuario', 'name', 'email', 'password']);
    }

    /**
     * Test: Crear usuario valida email único
     */
    public function test_store_validates_unique_email(): void
    {
        $admin = $this->createUser('SuperAdmin');

        // Crear usuario existente
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'usuario' => 'newuser',
            'name' => 'New User',
            'email' => 'existing@example.com', // Email duplicado
            'password' => 'Password123!',
            'sexo_id' => 1,
            'telefono' => '12345678',
            'chatid' => '987654321',
        ];

        $response = $this->authenticatedJson('POST', '/api/users', $userData, $admin);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test: Mostrar un usuario específico
     */
    public function test_show_returns_user_details(): void
    {
        $admin = $this->createUser('SuperAdmin');
        $user = User::factory()->create();

        $response = $this->authenticatedJson('GET', "/api/users/{$user->id}", [], $admin);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'usuario' => $user->usuario,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test: Mostrar usuario inexistente devuelve 404
     */
    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $response = $this->authenticatedJson('GET', '/api/users/99999', [], $admin);

        $response->assertStatus(404);
    }

    /**
     * Test: Actualizar usuario exitosamente
     */
    public function test_update_modifies_user_successfully(): void
    {
        $admin = $this->createUser('SuperAdmin');
        $user = User::factory()->create();

        $updateData = [
            'usuario' => 'updateduser',
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'sexo_id' => 2,
            'telefono' => '87654321',
            'chatid' => '123456789',
            'activo' => false,
        ];

        $response = $this->authenticatedJson('PUT', "/api/users/{$user->id}", $updateData, $admin);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
            ]);

        // Verificar que los datos fueron actualizados
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'usuario' => 'updateduser',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test: Actualizar usuario puede cambiar contraseña
     */
    public function test_update_can_change_password(): void
    {
        $admin = $this->createUser('SuperAdmin');
        $user = User::factory()->create();

        $updateData = [
            'usuario' => $user->usuario,
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            'sexo_id' => $user->sexo_id,
            'telefono' => $user->telefono,
            'chatid' => $user->chatid,
            'activo' => $user->activo,
        ];

        $response = $this->authenticatedJson('PUT', "/api/users/{$user->id}", $updateData, $admin);

        $response->assertStatus(200);

        // Verificar que la contraseña fue actualizada
        $user->refresh();
        $this->assertTrue(\Hash::check('NewPassword123!', $user->password));
    }

    /**
     * Test: Actualizar usuario valida email único
     */
    public function test_update_validates_unique_email(): void
    {
        $admin = $this->createUser('SuperAdmin');
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $updateData = [
            'usuario' => $user1->usuario,
            'name' => $user1->name,
            'email' => 'user2@example.com', // Email ya usado por user2
            'sexo_id' => $user1->sexo_id,
            'telefono' => $user1->telefono,
            'chatid' => $user1->chatid,
            'activo' => $user1->activo,
        ];

        $response = $this->authenticatedJson('PUT', "/api/users/{$user1->id}", $updateData, $admin);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test: Eliminar usuario exitosamente
     */
    public function test_destroy_deletes_user_successfully(): void
    {
        $admin = $this->createUser('SuperAdmin');
        $user = User::factory()->create();

        $response = $this->authenticatedJson('DELETE', "/api/users/{$user->id}", [], $admin);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente',
            ]);

        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test: No se puede eliminar a sí mismo
     */
    public function test_destroy_cannot_delete_self(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $response = $this->authenticatedJson('DELETE', "/api/users/{$admin->id}", [], $admin);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No puedes eliminarte a ti mismo',
            ]);

        // Verificar que el usuario NO fue eliminado
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    /**
     * Test: Eliminar múltiples usuarios (bulk delete)
     */
    public function test_destroy_bulk_deletes_multiple_users(): void
    {
        $admin = $this->createUser('SuperAdmin');
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $response = $this->authenticatedJson('DELETE', '/api/users/bulk/delete', [
            'ids' => [$user1->id, $user2->id, $user3->id],
        ], $admin);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'deleted_count' => 3,
            ]);

        // Verificar que los usuarios fueron eliminados
        $this->assertDatabaseMissing('users', ['id' => $user1->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('users', ['id' => $user3->id, 'deleted_at' => null]);
    }

    /**
     * Test: Bulk delete no puede eliminar al usuario autenticado
     */
    public function test_destroy_bulk_cannot_delete_self(): void
    {
        $admin = $this->createUser('SuperAdmin');
        $user1 = User::factory()->create();

        $response = $this->authenticatedJson('DELETE', '/api/users/bulk/delete', [
            'ids' => [$admin->id, $user1->id],
        ], $admin);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No puedes eliminarte a ti mismo',
            ]);
    }

    /**
     * Test: Cambiar contraseña propia exitosamente
     */
    public function test_update_password_changes_own_password(): void
    {
        $user = $this->createUser('Usuario', ['password' => bcrypt('OldPassword123!')]);

        $response = $this->authenticatedJson('PUT', "/api/users/{$user->id}/password", [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ], $user);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente',
            ]);

        // Verificar que la contraseña fue actualizada
        $user->refresh();
        $this->assertTrue(\Hash::check('NewPassword123!', $user->password));
    }

    /**
     * Test: Cambiar contraseña falla con contraseña actual incorrecta
     */
    public function test_update_password_fails_with_wrong_current_password(): void
    {
        $user = $this->createUser('Usuario', ['password' => bcrypt('OldPassword123!')]);

        $response = $this->authenticatedJson('PUT', "/api/users/{$user->id}/password", [
            'current_password' => 'WrongPassword!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ], $user);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta',
            ]);
    }

    /**
     * Test: No se puede cambiar contraseña de otro usuario
     */
    public function test_update_password_cannot_change_other_user_password(): void
    {
        $user1 = $this->createUser('Usuario', ['password' => bcrypt('Password123!')]);
        $user2 = User::factory()->create(['password' => bcrypt('Password123!')]);

        $response = $this->authenticatedJson('PUT', "/api/users/{$user2->id}/password", [
            'current_password' => 'Password123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ], $user1);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes permiso para cambiar esta contraseña',
            ]);
    }

    /**
     * Test: Eliminar avatar propio exitosamente
     */
    public function test_delete_avatar_removes_own_avatar(): void
    {
        Storage::fake('public');

        $user = $this->createUser('Usuario');

        // Simular que el usuario tiene un avatar
        $user->update(['avatar' => 'avatars/test-avatar.jpg']);
        Storage::disk('public')->put('avatars/test-avatar.jpg', 'fake-content');

        $response = $this->authenticatedJson('DELETE', "/api/users/{$user->id}/avatar", [], $user);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Avatar eliminado exitosamente',
            ]);

        // Verificar que el avatar fue eliminado
        $user->refresh();
        $this->assertNull($user->avatar);
        Storage::disk('public')->assertMissing('avatars/test-avatar.jpg');
    }

    /**
     * Test: No se puede eliminar avatar de otro usuario
     */
    public function test_delete_avatar_cannot_delete_other_user_avatar(): void
    {
        $user1 = $this->createUser('Usuario');
        $user2 = User::factory()->create(['avatar' => 'avatars/user2-avatar.jpg']);

        $response = $this->authenticatedJson('DELETE', "/api/users/{$user2->id}/avatar", [], $user1);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este avatar',
            ]);
    }
}
