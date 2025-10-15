<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Listar roles con paginación
     */
    public function test_index_returns_paginated_roles(): void
    {
        $admin = $this->createUser('SuperAdmin');

        // Los roles del sistema ya están creados por el seeder
        $response = $this->authenticatedJson('GET', '/api/roles', [], $admin);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'permissions'],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        // Debe incluir los roles del sistema
        $this->assertGreaterThanOrEqual(6, $response->json('meta.total'));
    }

    /**
     * Test: Búsqueda de roles funciona correctamente
     */
    public function test_index_search_filters_roles(): void
    {
        $admin = $this->createUser('SuperAdmin');

        // Crear un rol personalizado
        Role::create(['name' => 'Gerente de Ventas', 'guard_name' => 'api']);

        // Buscar por "Gerente"
        $response = $this->authenticatedJson('GET', '/api/roles?search=Gerente', [], $admin);

        $response->assertStatus(200);

        $roles = collect($response->json('data'));
        $this->assertTrue($roles->contains('name', 'Gerente de Ventas'));
    }

    /**
     * Test: Mostrar un rol específico con permisos
     */
    public function test_show_returns_role_with_permissions(): void
    {
        $admin = $this->createUser('SuperAdmin');

        // Crear rol con permisos
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'api']);
        $permission = Permission::where('name', 'users.view')->first();
        $role->givePermissionTo($permission);

        $response = $this->authenticatedJson('GET', "/api/roles/{$role->id}", [], $admin);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $role->id,
                    'name' => 'Editor',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'permissions' => [
                        '*' => ['id', 'name'],
                    ],
                ],
            ]);
    }

    /**
     * Test: Mostrar rol inexistente devuelve 404
     */
    public function test_show_returns_404_for_nonexistent_role(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $response = $this->authenticatedJson('GET', '/api/roles/99999', [], $admin);

        $response->assertStatus(404);
    }

    /**
     * Test: Crear rol exitosamente sin permisos
     */
    public function test_store_creates_role_without_permissions(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $roleData = [
            'name' => 'Nuevo Rol',
        ];

        $response = $this->authenticatedJson('POST', '/api/roles', $roleData, $admin);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Rol creado exitosamente',
                'data' => [
                    'name' => 'Nuevo Rol',
                ],
            ]);

        // Verificar que el rol fue creado en la base de datos
        $this->assertDatabaseHas('roles', [
            'name' => 'Nuevo Rol',
            'guard_name' => 'api',
        ]);
    }

    /**
     * Test: Crear rol exitosamente con permisos
     */
    public function test_store_creates_role_with_permissions(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $roleData = [
            'name' => 'Rol Con Permisos',
            'permissions' => ['users.index', 'users.store'],
        ];

        $response = $this->authenticatedJson('POST', '/api/roles', $roleData, $admin);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Rol creado exitosamente',
            ]);

        // Verificar que el rol tiene los permisos asignados
        $role = Role::where('name', 'Rol Con Permisos')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('users.index'));
        $this->assertTrue($role->hasPermissionTo('users.store'));
    }

    /**
     * Test: Crear rol requiere autenticación
     */
    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/roles', [
            'name' => 'Test Role',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test: Crear rol valida nombre requerido
     */
    public function test_store_validates_required_name(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $response = $this->authenticatedJson('POST', '/api/roles', [], $admin);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Crear rol valida nombre único
     */
    public function test_store_validates_unique_name(): void
    {
        $admin = $this->createUser('SuperAdmin');

        // El rol SuperAdmin ya existe del seeder
        $roleData = [
            'name' => 'SuperAdmin',
        ];

        $response = $this->authenticatedJson('POST', '/api/roles', $roleData, $admin);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Crear rol valida permisos válidos
     */
    public function test_store_validates_valid_permissions(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $roleData = [
            'name' => 'Rol Test',
            'permissions' => ['permiso.invalido'],
        ];

        $response = $this->authenticatedJson('POST', '/api/roles', $roleData, $admin);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['permissions.0']);
    }

    /**
     * Test: Actualizar rol exitosamente
     */
    public function test_update_modifies_role_successfully(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $role = Role::create(['name' => 'Rol Original', 'guard_name' => 'api']);

        $updateData = [
            'name' => 'Rol Actualizado',
            'permissions' => ['users.index'],
        ];

        $response = $this->authenticatedJson('PUT', "/api/roles/{$role->id}", $updateData, $admin);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Rol actualizado exitosamente',
            ]);

        // Verificar que los datos fueron actualizados
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Rol Actualizado',
        ]);

        // Verificar permisos
        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('users.index'));
    }

    /**
     * Test: No se puede modificar el rol SuperAdmin
     */
    public function test_update_cannot_modify_superadmin_role(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $superAdminRole = Role::where('name', 'SuperAdmin')->first();

        $updateData = [
            'name' => 'Intento de Cambio',
        ];

        $response = $this->authenticatedJson('PUT', "/api/roles/{$superAdminRole->id}", $updateData, $admin);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No se puede modificar el rol SuperAdmin',
            ]);

        // Verificar que el rol NO fue modificado
        $this->assertDatabaseHas('roles', [
            'id' => $superAdminRole->id,
            'name' => 'SuperAdmin',
        ]);
    }

    /**
     * Test: Actualizar rol valida nombre único
     */
    public function test_update_validates_unique_name(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $role1 = Role::create(['name' => 'Rol Uno', 'guard_name' => 'api']);
        $role2 = Role::create(['name' => 'Rol Dos', 'guard_name' => 'api']);

        $updateData = [
            'name' => 'Rol Dos', // Nombre ya usado por role2
        ];

        $response = $this->authenticatedJson('PUT', "/api/roles/{$role1->id}", $updateData, $admin);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Eliminar rol personalizado exitosamente
     */
    public function test_destroy_deletes_custom_role_successfully(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $role = Role::create(['name' => 'Rol Temporal', 'guard_name' => 'api']);

        $response = $this->authenticatedJson('DELETE', "/api/roles/{$role->id}", [], $admin);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Rol eliminado exitosamente',
            ]);

        // Verificar que el rol fue eliminado
        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    /**
     * Test: No se pueden eliminar roles del sistema
     */
    public function test_destroy_cannot_delete_system_roles(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $systemRoles = ['SuperAdmin', 'Administrador', 'Supervisor', 'Vendedor', 'Usuario', 'Contabilidad'];

        foreach ($systemRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();

            $response = $this->authenticatedJson('DELETE', "/api/roles/{$role->id}", [], $admin);

            $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'No se pueden eliminar los roles del sistema',
                ]);

            // Verificar que el rol NO fue eliminado
            $this->assertDatabaseHas('roles', [
                'id' => $role->id,
                'name' => $roleName,
            ]);
        }
    }

    /**
     * Test: No se puede eliminar rol con usuarios asignados
     */
    public function test_destroy_cannot_delete_role_with_users(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $role = Role::create(['name' => 'Rol Con Usuarios', 'guard_name' => 'api']);

        // Asignar el rol a un usuario
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->authenticatedJson('DELETE', "/api/roles/{$role->id}", [], $admin);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'No se puede eliminar el rol porque hay usuarios asignados',
            ]);

        // Verificar que el rol NO fue eliminado
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    /**
     * Test: Obtener todos los permisos disponibles
     */
    public function test_all_permissions_returns_grouped_permissions(): void
    {
        $admin = $this->createUser('SuperAdmin');

        $response = $this->authenticatedJson('GET', '/api/roles/permissions', [], $admin);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'total',
            ]);

        // Verificar que los permisos están agrupados por módulo
        $data = $response->json('data');
        $this->assertIsArray($data);

        // Debe incluir al menos el módulo 'users'
        $this->assertArrayHasKey('users', $data);
    }

    /**
     * Test: Listar roles requiere autenticación
     */
    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/roles');

        $response->assertStatus(401);
    }

    /**
     * Test: Actualizar rol requiere autenticación
     */
    public function test_update_requires_authentication(): void
    {
        $role = Role::create(['name' => 'Test Role', 'guard_name' => 'api']);

        $response = $this->putJson("/api/roles/{$role->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test: Eliminar rol requiere autenticación
     */
    public function test_destroy_requires_authentication(): void
    {
        $role = Role::create(['name' => 'Test Role', 'guard_name' => 'api']);

        $response = $this->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(401);
    }
}
