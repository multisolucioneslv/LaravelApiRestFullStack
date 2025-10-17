<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    protected $empresa;
    protected $user;
    protected $categoria;

    /**
     * Setup ejecutado antes de cada test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Crear empresa de prueba
        $this->empresa = Empresa::find(1); // La empresa creada en TestCase

        // Crear usuario con permisos de productos
        $this->user = $this->createUser('SuperAdmin', ['empresa_id' => $this->empresa->id]);

        // Dar permisos específicos de productos
        $this->user->givePermissionTo([
            'productos.index',
            'productos.create',
            'productos.update',
            'productos.delete',
            'productos.restore',
            'productos.categorias.sync',
        ]);

        // Crear una categoría de prueba
        $this->categoria = Categoria::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);
    }

    // ==========================================
    // TESTS: INDEX - Listar Productos
    // ==========================================

    /**
     * Test: Puede listar productos paginados
     */
    public function test_puede_listar_productos_paginados(): void
    {
        // Crear 15 productos para probar paginación
        Producto::factory()->count(15)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('GET', '/api/productos', [], $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'nombre',
                        'sku',
                        'precio_venta',
                        'stock_actual',
                        'activo',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'links',
            ]);

        // Verificar que devuelve 10 items por página (configuración por defecto)
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(15, $response->json('meta.total'));
    }

    /**
     * Test: Solo muestra productos de su empresa (multi-tenancy)
     */
    public function test_solo_muestra_productos_de_su_empresa(): void
    {
        // Crear productos de esta empresa
        Producto::factory()->count(3)->create(['empresa_id' => $this->empresa->id]);

        // Crear otra empresa y sus productos
        $otraEmpresa = Empresa::factory()->create();
        Producto::factory()->count(5)->create(['empresa_id' => $otraEmpresa->id]);

        $response = $this->authenticatedJson('GET', '/api/productos', [], $this->user);

        $response->assertStatus(200);

        // Debe devolver solo 3 productos (los de esta empresa)
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test: Puede filtrar productos por búsqueda
     */
    public function test_puede_filtrar_productos_por_busqueda(): void
    {
        Producto::factory()->create([
            'nombre' => 'Laptop Dell',
            'empresa_id' => $this->empresa->id,
        ]);
        Producto::factory()->create([
            'nombre' => 'Mouse Logitech',
            'empresa_id' => $this->empresa->id,
        ]);
        Producto::factory()->create([
            'nombre' => 'Teclado Mecánico',
            'empresa_id' => $this->empresa->id,
        ]);

        $response = $this->authenticatedJson('GET', '/api/productos?search=laptop', [], $this->user);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertStringContainsString('Laptop', $response->json('data.0.nombre'));
    }

    /**
     * Test: Requiere permiso para listar productos
     */
    public function test_requiere_permiso_para_listar_productos(): void
    {
        // Crear usuario sin permisos
        $userSinPermiso = $this->createUser('Usuario', ['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('GET', '/api/productos', [], $userSinPermiso);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ]);
    }

    // ==========================================
    // TESTS: STORE - Crear Producto
    // ==========================================

    /**
     * Test: Puede crear producto con datos válidos
     */
    public function test_puede_crear_producto_con_datos_validos(): void
    {
        $productoData = [
            'nombre' => 'Producto Test',
            'descripcion' => 'Descripción del producto test',
            'sku' => 'TEST-001',
            'precio_venta' => 100.50,
            'stock_actual' => 50,
            'categorias' => [$this->categoria->id],
        ];

        $response = $this->authenticatedJson('POST', '/api/productos', $productoData, $this->user);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'nombre',
                    'sku',
                    'precio_venta',
                    'stock_actual',
                ],
            ]);

        // Verificar que se creó en la base de datos
        $this->assertDatabaseHas('productos', [
            'sku' => 'TEST-001',
            'nombre' => 'Producto Test',
            'empresa_id' => $this->empresa->id,
        ]);

        // Verificar que se asignó la categoría
        $producto = Producto::where('sku', 'TEST-001')->first();
        $this->assertTrue($producto->categorias->contains($this->categoria));
    }

    /**
     * Test: Validación - nombre es requerido
     */
    public function test_validacion_nombre_es_requerido(): void
    {
        $productoData = [
            'sku' => 'TEST-001',
            'precio_venta' => 100.50,
            'stock_actual' => 50,
        ];

        $response = $this->authenticatedJson('POST', '/api/productos', $productoData, $this->user);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    /**
     * Test: Validación - SKU debe ser único por empresa
     */
    public function test_validacion_sku_debe_ser_unico_por_empresa(): void
    {
        // Crear producto existente con SKU
        Producto::factory()->create([
            'sku' => 'SKU-EXISTENTE',
            'empresa_id' => $this->empresa->id,
        ]);

        $productoData = [
            'nombre' => 'Producto Nuevo',
            'sku' => 'SKU-EXISTENTE', // SKU duplicado
            'precio_venta' => 100.50,
            'stock_actual' => 50,
        ];

        $response = $this->authenticatedJson('POST', '/api/productos', $productoData, $this->user);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    /**
     * Test: Puede crear producto sin categorías
     */
    public function test_puede_crear_producto_sin_categorias(): void
    {
        $productoData = [
            'nombre' => 'Producto Sin Categoría',
            'sku' => 'TEST-002',
            'precio_venta' => 100.50,
            'stock_actual' => 50,
        ];

        $response = $this->authenticatedJson('POST', '/api/productos', $productoData, $this->user);

        $response->assertStatus(201);

        $producto = Producto::where('sku', 'TEST-002')->first();
        $this->assertEquals(0, $producto->categorias->count());
    }

    // ==========================================
    // TESTS: SHOW - Mostrar Producto
    // ==========================================

    /**
     * Test: Puede ver un producto específico
     */
    public function test_puede_ver_producto_especifico(): void
    {
        $producto = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Producto Visible',
        ]);

        $response = $this->authenticatedJson('GET', "/api/productos/{$producto->id}", [], $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'nombre',
                    'sku',
                    'precio_venta',
                    'stock_actual',
                    'categorias',
                ],
            ])
            ->assertJsonFragment([
                'nombre' => 'Producto Visible',
            ]);
    }

    /**
     * Test: No puede ver productos de otra empresa
     */
    public function test_no_puede_ver_productos_de_otra_empresa(): void
    {
        // Crear otra empresa y su producto
        $otraEmpresa = Empresa::factory()->create();
        $producto = Producto::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $response = $this->authenticatedJson('GET', "/api/productos/{$producto->id}", [], $this->user);

        $response->assertStatus(404);
    }

    // ==========================================
    // TESTS: UPDATE - Actualizar Producto
    // ==========================================

    /**
     * Test: Puede actualizar producto
     */
    public function test_puede_actualizar_producto(): void
    {
        $producto = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Producto Original',
        ]);

        $updateData = [
            'nombre' => 'Producto Actualizado',
            'precio_venta' => 150.00,
        ];

        $response = $this->authenticatedJson('PUT', "/api/productos/{$producto->id}", $updateData, $this->user);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'nombre' => 'Producto Actualizado',
            ]);

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'nombre' => 'Producto Actualizado',
        ]);
    }

    /**
     * Test: No puede actualizar productos de otra empresa
     */
    public function test_no_puede_actualizar_productos_de_otra_empresa(): void
    {
        $otraEmpresa = Empresa::factory()->create();
        $producto = Producto::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $updateData = ['nombre' => 'Intento de actualización'];

        $response = $this->authenticatedJson('PUT', "/api/productos/{$producto->id}", $updateData, $this->user);

        $response->assertStatus(404);
    }

    // ==========================================
    // TESTS: DELETE - Eliminar Producto (Soft Delete)
    // ==========================================

    /**
     * Test: Puede eliminar producto (soft delete)
     */
    public function test_puede_eliminar_producto_soft_delete(): void
    {
        $producto = Producto::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('DELETE', "/api/productos/{$producto->id}", [], $this->user);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Producto eliminado exitosamente.',
            ]);

        // Verificar que se eliminó (soft delete)
        $this->assertSoftDeleted('productos', ['id' => $producto->id]);
    }

    /**
     * Test: No puede eliminar productos de otra empresa
     */
    public function test_no_puede_eliminar_productos_de_otra_empresa(): void
    {
        $otraEmpresa = Empresa::factory()->create();
        $producto = Producto::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $response = $this->authenticatedJson('DELETE', "/api/productos/{$producto->id}", [], $this->user);

        $response->assertStatus(404);
    }

    // ==========================================
    // TESTS: RESTORE - Restaurar Producto
    // ==========================================

    /**
     * Test: Puede restaurar producto eliminado
     */
    public function test_puede_restaurar_producto_eliminado(): void
    {
        $producto = Producto::factory()->create(['empresa_id' => $this->empresa->id]);
        $producto->delete(); // Soft delete

        $response = $this->authenticatedJson('POST', "/api/productos/{$producto->id}/restore", [], $this->user);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Producto restaurado exitosamente.',
            ]);

        // Verificar que se restauró
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'deleted_at' => null,
        ]);
    }

    // ==========================================
    // TESTS: CATEGORIAS - Sincronizar Categorías
    // ==========================================

    /**
     * Test: Puede sincronizar categorías de un producto
     */
    public function test_puede_sincronizar_categorias_de_producto(): void
    {
        $producto = Producto::factory()->create(['empresa_id' => $this->empresa->id]);

        $categoria1 = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);
        $categoria2 = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('POST', "/api/productos/{$producto->id}/categorias", [
            'categorias' => [$categoria1->id, $categoria2->id],
        ], $this->user);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Categorías sincronizadas exitosamente.',
            ]);

        $producto->refresh();
        $this->assertCount(2, $producto->categorias);
    }

    /**
     * Test: No puede sincronizar categorías de otra empresa
     */
    public function test_no_puede_sincronizar_categorias_de_otra_empresa(): void
    {
        $producto = Producto::factory()->create(['empresa_id' => $this->empresa->id]);

        $otraEmpresa = Empresa::factory()->create();
        $categoriaOtraEmpresa = Categoria::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $response = $this->authenticatedJson('POST', "/api/productos/{$producto->id}/categorias", [
            'categorias' => [$categoriaOtraEmpresa->id],
        ], $this->user);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['categorias.0']);
    }

    // ==========================================
    // TESTS: MÉTODOS DE UTILIDAD DEL MODELO
    // ==========================================

    /**
     * Test: Método esBajoStock funciona correctamente
     */
    public function test_metodo_es_bajo_stock_funciona(): void
    {
        $producto = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'stock_minimo' => 20,
            'stock_actual' => 10,
        ]);

        $this->assertTrue($producto->esBajoStock());

        $producto->update(['stock_actual' => 30]);
        $this->assertFalse($producto->esBajoStock());
    }

    /**
     * Test: Método estaDisponible funciona correctamente
     */
    public function test_metodo_esta_disponible_funciona(): void
    {
        $productoDisponible = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'activo' => true,
            'stock_actual' => 10,
        ]);

        $this->assertTrue($productoDisponible->estaDisponible());

        $productoSinStock = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'activo' => true,
            'stock_actual' => 0,
        ]);

        $this->assertFalse($productoSinStock->estaDisponible());
    }

    /**
     * Test: Método calcularMargenGanancia funciona correctamente
     */
    public function test_metodo_calcular_margen_ganancia_funciona(): void
    {
        $producto = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'precio_compra' => 100,
            'precio_venta' => 150,
        ]);

        // Margen = ((150 - 100) / 100) * 100 = 50%
        $this->assertEquals(50, $producto->calcularMargenGanancia());
    }
}
