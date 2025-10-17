<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductoPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $empresa;
    protected $producto;

    /**
     * Setup ejecutado antes de cada test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Crear empresa de prueba
        $this->empresa = Empresa::find(1);

        // Crear producto de prueba
        $this->producto = Producto::factory()->create(['empresa_id' => $this->empresa->id]);
    }

    // ==========================================
    // TESTS: Permisos por Rol - INDEX
    // ==========================================

    /**
     * Test: SuperAdmin puede listar productos
     */
    public function test_superadmin_puede_listar_productos(): void
    {
        $superAdmin = $this->createUser('SuperAdmin', ['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('GET', '/api/productos', [], $superAdmin);

        $response->assertStatus(200);
    }

    /**
     * Test: Administrador puede listar productos si tiene permiso
     */
    public function test_administrador_puede_listar_productos_con_permiso(): void
    {
        $admin = $this->createUser('Administrador', ['empresa_id' => $this->empresa->id]);
        $admin->givePermissionTo('productos.index');

        $response = $this->authenticatedJson('GET', '/api/productos', [], $admin);

        $response->assertStatus(200);
    }

    /**
     * Test: Usuario sin permiso no puede listar productos
     */
    public function test_usuario_sin_permiso_no_puede_listar_productos(): void
    {
        $usuario = $this->createUser('Usuario', ['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('GET', '/api/productos', [], $usuario);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ]);
    }

    // ==========================================
    // TESTS: Permisos por Rol - CREATE
    // ==========================================

    /**
     * Test: Usuario con permiso productos.create puede crear producto
     */
    public function test_usuario_con_permiso_create_puede_crear_producto(): void
    {
        $usuario = $this->createUser('Vendedor', ['empresa_id' => $this->empresa->id]);
        $usuario->givePermissionTo('productos.create');

        $productoData = [
            'nombre' => 'Producto Test',
            'sku' => 'TEST-001',
            'precio_venta' => 100.50,
            'stock_actual' => 50,
        ];

        $response = $this->authenticatedJson('POST', '/api/productos', $productoData, $usuario);

        $response->assertStatus(201);
    }

    /**
     * Test: Usuario sin permiso productos.create no puede crear producto
     */
    public function test_usuario_sin_permiso_create_no_puede_crear_producto(): void
    {
        $usuario = $this->createUser('Usuario', ['empresa_id' => $this->empresa->id]);

        $productoData = [
            'nombre' => 'Producto Test',
            'sku' => 'TEST-001',
            'precio_venta' => 100.50,
            'stock_actual' => 50,
        ];

        $response = $this->authenticatedJson('POST', '/api/productos', $productoData, $usuario);

        $response->assertStatus(403);
    }

    // ==========================================
    // TESTS: Permisos por Rol - UPDATE
    // ==========================================

    /**
     * Test: Usuario con permiso productos.update puede actualizar producto
     */
    public function test_usuario_con_permiso_update_puede_actualizar_producto(): void
    {
        $usuario = $this->createUser('Supervisor', ['empresa_id' => $this->empresa->id]);
        $usuario->givePermissionTo('productos.update');

        $updateData = ['nombre' => 'Producto Actualizado'];

        $response = $this->authenticatedJson('PUT', "/api/productos/{$this->producto->id}", $updateData, $usuario);

        $response->assertStatus(200);
    }

    /**
     * Test: Usuario sin permiso productos.update no puede actualizar producto
     */
    public function test_usuario_sin_permiso_update_no_puede_actualizar_producto(): void
    {
        $usuario = $this->createUser('Usuario', ['empresa_id' => $this->empresa->id]);

        $updateData = ['nombre' => 'Producto Actualizado'];

        $response = $this->authenticatedJson('PUT', "/api/productos/{$this->producto->id}", $updateData, $usuario);

        $response->assertStatus(403);
    }

    // ==========================================
    // TESTS: Permisos por Rol - DELETE
    // ==========================================

    /**
     * Test: Usuario con permiso productos.delete puede eliminar producto
     */
    public function test_usuario_con_permiso_delete_puede_eliminar_producto(): void
    {
        $usuario = $this->createUser('Administrador', ['empresa_id' => $this->empresa->id]);
        $usuario->givePermissionTo('productos.delete');

        $response = $this->authenticatedJson('DELETE', "/api/productos/{$this->producto->id}", [], $usuario);

        $response->assertStatus(200);
    }

    /**
     * Test: Usuario sin permiso productos.delete no puede eliminar producto
     */
    public function test_usuario_sin_permiso_delete_no_puede_eliminar_producto(): void
    {
        $usuario = $this->createUser('Usuario', ['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('DELETE', "/api/productos/{$this->producto->id}", [], $usuario);

        $response->assertStatus(403);
    }

    // ==========================================
    // TESTS: Permisos por Rol - RESTORE
    // ==========================================

    /**
     * Test: Usuario con permiso productos.restore puede restaurar producto
     */
    public function test_usuario_con_permiso_restore_puede_restaurar_producto(): void
    {
        $this->producto->delete(); // Soft delete

        $usuario = $this->createUser('Administrador', ['empresa_id' => $this->empresa->id]);
        $usuario->givePermissionTo('productos.restore');

        $response = $this->authenticatedJson('POST', "/api/productos/{$this->producto->id}/restore", [], $usuario);

        $response->assertStatus(200);
    }

    /**
     * Test: Usuario sin permiso productos.restore no puede restaurar producto
     */
    public function test_usuario_sin_permiso_restore_no_puede_restaurar_producto(): void
    {
        $this->producto->delete(); // Soft delete

        $usuario = $this->createUser('Usuario', ['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('POST', "/api/productos/{$this->producto->id}/restore", [], $usuario);

        $response->assertStatus(403);
    }

    // ==========================================
    // TESTS: Multi-Tenancy (Aislamiento por Empresa)
    // ==========================================

    /**
     * Test: Usuario no puede ver productos de otra empresa aunque tenga permiso
     */
    public function test_usuario_no_puede_ver_productos_de_otra_empresa(): void
    {
        // Crear otra empresa y su producto
        $otraEmpresa = Empresa::factory()->create();
        $productoOtraEmpresa = Producto::factory()->create(['empresa_id' => $otraEmpresa->id]);

        // Crear usuario de la primera empresa con todos los permisos
        $usuario = $this->createUser('SuperAdmin', ['empresa_id' => $this->empresa->id]);

        // Intentar ver el producto de otra empresa
        $response = $this->authenticatedJson('GET', "/api/productos/{$productoOtraEmpresa->id}", [], $usuario);

        $response->assertStatus(404);
    }

    /**
     * Test: Usuario no puede actualizar productos de otra empresa aunque tenga permiso
     */
    public function test_usuario_no_puede_actualizar_productos_de_otra_empresa(): void
    {
        // Crear otra empresa y su producto
        $otraEmpresa = Empresa::factory()->create();
        $productoOtraEmpresa = Producto::factory()->create(['empresa_id' => $otraEmpresa->id]);

        // Crear usuario con permiso de actualizar
        $usuario = $this->createUser('SuperAdmin', ['empresa_id' => $this->empresa->id]);

        $updateData = ['nombre' => 'Intento de actualizar'];

        $response = $this->authenticatedJson('PUT', "/api/productos/{$productoOtraEmpresa->id}", $updateData, $usuario);

        $response->assertStatus(404);
    }

    /**
     * Test: Usuario no puede eliminar productos de otra empresa aunque tenga permiso
     */
    public function test_usuario_no_puede_eliminar_productos_de_otra_empresa(): void
    {
        // Crear otra empresa y su producto
        $otraEmpresa = Empresa::factory()->create();
        $productoOtraEmpresa = Producto::factory()->create(['empresa_id' => $otraEmpresa->id]);

        // Crear usuario con permiso de eliminar
        $usuario = $this->createUser('SuperAdmin', ['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('DELETE', "/api/productos/{$productoOtraEmpresa->id}", [], $usuario);

        $response->assertStatus(404);
    }

    // ==========================================
    // TESTS: Sincronizar Categorías (Permisos)
    // ==========================================

    /**
     * Test: Usuario con permiso puede sincronizar categorías
     */
    public function test_usuario_con_permiso_puede_sincronizar_categorias(): void
    {
        $categoria = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);

        $usuario = $this->createUser('Administrador', ['empresa_id' => $this->empresa->id]);
        $usuario->givePermissionTo('productos.categorias.sync');

        $response = $this->authenticatedJson('POST', "/api/productos/{$this->producto->id}/categorias", [
            'categorias' => [$categoria->id],
        ], $usuario);

        $response->assertStatus(200);
    }

    /**
     * Test: Usuario sin permiso no puede sincronizar categorías
     */
    public function test_usuario_sin_permiso_no_puede_sincronizar_categorias(): void
    {
        $categoria = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);

        $usuario = $this->createUser('Usuario', ['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('POST', "/api/productos/{$this->producto->id}/categorias", [
            'categorias' => [$categoria->id],
        ], $usuario);

        $response->assertStatus(403);
    }

    /**
     * Test: No puede sincronizar categorías de otra empresa
     */
    public function test_no_puede_sincronizar_categorias_de_otra_empresa(): void
    {
        // Crear otra empresa y su categoría
        $otraEmpresa = Empresa::factory()->create();
        $categoriaOtraEmpresa = Categoria::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $usuario = $this->createUser('SuperAdmin', ['empresa_id' => $this->empresa->id]);
        $usuario->givePermissionTo('productos.categorias.sync');

        $response = $this->authenticatedJson('POST', "/api/productos/{$this->producto->id}/categorias", [
            'categorias' => [$categoriaOtraEmpresa->id],
        ], $usuario);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['categorias.0']);
    }

    // ==========================================
    // TESTS: Combinación de Permisos
    // ==========================================

    /**
     * Test: SuperAdmin tiene todos los permisos de productos
     */
    public function test_superadmin_tiene_todos_los_permisos_de_productos(): void
    {
        $superAdmin = $this->createUser('SuperAdmin', ['empresa_id' => $this->empresa->id]);

        // Verificar que puede listar
        $response1 = $this->authenticatedJson('GET', '/api/productos', [], $superAdmin);
        $response1->assertStatus(200);

        // Verificar que puede crear
        $response2 = $this->authenticatedJson('POST', '/api/productos', [
            'nombre' => 'Test',
            'sku' => 'TEST-999',
            'precio_venta' => 100,
            'stock_actual' => 10,
        ], $superAdmin);
        $response2->assertStatus(201);

        // Verificar que puede actualizar
        $response3 = $this->authenticatedJson('PUT', "/api/productos/{$this->producto->id}", [
            'nombre' => 'Actualizado',
        ], $superAdmin);
        $response3->assertStatus(200);

        // Verificar que puede eliminar
        $producto = Producto::factory()->create(['empresa_id' => $this->empresa->id]);
        $response4 = $this->authenticatedJson('DELETE', "/api/productos/{$producto->id}", [], $superAdmin);
        $response4->assertStatus(200);
    }

    /**
     * Test: Usuario con rol personalizado puede tener permisos selectivos
     */
    public function test_usuario_con_rol_personalizado_permisos_selectivos(): void
    {
        $usuario = $this->createUser('Vendedor', ['empresa_id' => $this->empresa->id]);

        // Dar solo permisos de listar y crear
        $usuario->givePermissionTo(['productos.index', 'productos.create']);

        // Puede listar
        $response1 = $this->authenticatedJson('GET', '/api/productos', [], $usuario);
        $response1->assertStatus(200);

        // Puede crear
        $response2 = $this->authenticatedJson('POST', '/api/productos', [
            'nombre' => 'Test',
            'sku' => 'TEST-888',
            'precio_venta' => 100,
            'stock_actual' => 10,
        ], $usuario);
        $response2->assertStatus(201);

        // No puede actualizar
        $response3 = $this->authenticatedJson('PUT', "/api/productos/{$this->producto->id}", [
            'nombre' => 'Actualizado',
        ], $usuario);
        $response3->assertStatus(403);

        // No puede eliminar
        $response4 = $this->authenticatedJson('DELETE', "/api/productos/{$this->producto->id}", [], $usuario);
        $response4->assertStatus(403);
    }

    /**
     * Test: Usuario debe estar autenticado para acceder a cualquier endpoint
     */
    public function test_usuario_debe_estar_autenticado_para_acceder_endpoints(): void
    {
        // Sin autenticación - intentar listar
        $response1 = $this->getJson('/api/productos');
        $response1->assertStatus(401);

        // Sin autenticación - intentar crear
        $response2 = $this->postJson('/api/productos', [
            'nombre' => 'Test',
            'sku' => 'TEST-777',
            'precio_venta' => 100,
            'stock_actual' => 10,
        ]);
        $response2->assertStatus(401);

        // Sin autenticación - intentar actualizar
        $response3 = $this->putJson("/api/productos/{$this->producto->id}", [
            'nombre' => 'Actualizado',
        ]);
        $response3->assertStatus(401);

        // Sin autenticación - intentar eliminar
        $response4 = $this->deleteJson("/api/productos/{$this->producto->id}");
        $response4->assertStatus(401);
    }
}
