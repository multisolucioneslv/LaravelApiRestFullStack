<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    protected $empresa;
    protected $user;

    /**
     * Setup ejecutado antes de cada test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Crear empresa de prueba
        $this->empresa = Empresa::find(1); // La empresa creada en TestCase

        // Crear usuario con permisos de categorías
        $this->user = $this->createUser('SuperAdmin', ['empresa_id' => $this->empresa->id]);

        // Dar permisos específicos de categorías
        $this->user->givePermissionTo([
            'categorias.index',
            'categorias.create',
            'categorias.update',
            'categorias.delete',
            'categorias.restore',
            'categorias.productos',
        ]);
    }

    // ==========================================
    // TESTS: INDEX - Listar Categorías
    // ==========================================

    /**
     * Test: Puede listar categorías paginadas
     */
    public function test_puede_listar_categorias_paginadas(): void
    {
        // Crear 12 categorías para probar paginación
        Categoria::factory()->count(12)->create(['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('GET', '/api/categorias', [], $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'nombre',
                        'slug',
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

        // Verificar que devuelve 10 items por página
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(12, $response->json('meta.total'));
    }

    /**
     * Test: Solo muestra categorías de su empresa (multi-tenancy)
     */
    public function test_solo_muestra_categorias_de_su_empresa(): void
    {
        // Crear categorías de esta empresa
        Categoria::factory()->count(4)->create(['empresa_id' => $this->empresa->id]);

        // Crear otra empresa y sus categorías
        $otraEmpresa = Empresa::factory()->create();
        Categoria::factory()->count(6)->create(['empresa_id' => $otraEmpresa->id]);

        $response = $this->authenticatedJson('GET', '/api/categorias', [], $this->user);

        $response->assertStatus(200);

        // Debe devolver solo 4 categorías (las de esta empresa)
        $this->assertCount(4, $response->json('data'));
    }

    /**
     * Test: Puede filtrar categorías por búsqueda
     */
    public function test_puede_filtrar_categorias_por_busqueda(): void
    {
        Categoria::factory()->create([
            'nombre' => 'Electrónica',
            'empresa_id' => $this->empresa->id,
        ]);
        Categoria::factory()->create([
            'nombre' => 'Ropa',
            'empresa_id' => $this->empresa->id,
        ]);
        Categoria::factory()->create([
            'nombre' => 'Alimentos',
            'empresa_id' => $this->empresa->id,
        ]);

        $response = $this->authenticatedJson('GET', '/api/categorias?search=electrónica', [], $this->user);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertStringContainsString('Electrónica', $response->json('data.0.nombre'));
    }

    /**
     * Test: Requiere permiso para listar categorías
     */
    public function test_requiere_permiso_para_listar_categorias(): void
    {
        // Crear usuario sin permisos
        $userSinPermiso = $this->createUser('Usuario', ['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('GET', '/api/categorias', [], $userSinPermiso);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ]);
    }

    // ==========================================
    // TESTS: STORE - Crear Categoría
    // ==========================================

    /**
     * Test: Puede crear categoría con datos válidos
     */
    public function test_puede_crear_categoria_con_datos_validos(): void
    {
        $categoriaData = [
            'nombre' => 'Nueva Categoría',
            'descripcion' => 'Descripción de la categoría',
            'icono' => 'fas fa-tag',
            'color' => '#FF5733',
        ];

        $response = $this->authenticatedJson('POST', '/api/categorias', $categoriaData, $this->user);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'nombre',
                    'slug',
                    'icono',
                    'color',
                ],
            ]);

        // Verificar que se creó en la base de datos
        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Nueva Categoría',
            'slug' => 'nueva-categoria',
            'empresa_id' => $this->empresa->id,
        ]);
    }

    /**
     * Test: Genera slug automáticamente
     */
    public function test_genera_slug_automaticamente(): void
    {
        $categoriaData = [
            'nombre' => 'Categoría Con Espacios',
        ];

        $response = $this->authenticatedJson('POST', '/api/categorias', $categoriaData, $this->user);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'slug' => 'categoria-con-espacios',
            ]);
    }

    /**
     * Test: Validación - nombre es requerido
     */
    public function test_validacion_nombre_es_requerido(): void
    {
        $categoriaData = [
            'descripcion' => 'Sin nombre',
        ];

        $response = $this->authenticatedJson('POST', '/api/categorias', $categoriaData, $this->user);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    /**
     * Test: Validación - nombre debe ser único por empresa
     */
    public function test_validacion_nombre_debe_ser_unico_por_empresa(): void
    {
        // Crear categoría existente
        Categoria::factory()->create([
            'nombre' => 'Categoría Existente',
            'empresa_id' => $this->empresa->id,
        ]);

        $categoriaData = [
            'nombre' => 'Categoría Existente', // Nombre duplicado
        ];

        $response = $this->authenticatedJson('POST', '/api/categorias', $categoriaData, $this->user);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    /**
     * Test: Nombre puede repetirse en diferentes empresas
     */
    public function test_nombre_puede_repetirse_en_diferentes_empresas(): void
    {
        // Crear categoría en otra empresa
        $otraEmpresa = Empresa::factory()->create();
        Categoria::factory()->create([
            'nombre' => 'Categoría Global',
            'empresa_id' => $otraEmpresa->id,
        ]);

        // Crear la misma categoría en esta empresa (debe permitirse)
        $categoriaData = [
            'nombre' => 'Categoría Global',
        ];

        $response = $this->authenticatedJson('POST', '/api/categorias', $categoriaData, $this->user);

        $response->assertStatus(201);
    }

    // ==========================================
    // TESTS: SHOW - Mostrar Categoría
    // ==========================================

    /**
     * Test: Puede ver una categoría específica
     */
    public function test_puede_ver_categoria_especifica(): void
    {
        $categoria = Categoria::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Categoría Visible',
        ]);

        $response = $this->authenticatedJson('GET', "/api/categorias/{$categoria->id}", [], $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'nombre',
                    'slug',
                    'descripcion',
                    'icono',
                    'color',
                ],
            ])
            ->assertJsonFragment([
                'nombre' => 'Categoría Visible',
            ]);
    }

    /**
     * Test: No puede ver categorías de otra empresa
     */
    public function test_no_puede_ver_categorias_de_otra_empresa(): void
    {
        // Crear otra empresa y su categoría
        $otraEmpresa = Empresa::factory()->create();
        $categoria = Categoria::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $response = $this->authenticatedJson('GET', "/api/categorias/{$categoria->id}", [], $this->user);

        $response->assertStatus(404);
    }

    // ==========================================
    // TESTS: UPDATE - Actualizar Categoría
    // ==========================================

    /**
     * Test: Puede actualizar categoría
     */
    public function test_puede_actualizar_categoria(): void
    {
        $categoria = Categoria::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Categoría Original',
        ]);

        $updateData = [
            'nombre' => 'Categoría Actualizada',
            'color' => '#00FF00',
        ];

        $response = $this->authenticatedJson('PUT', "/api/categorias/{$categoria->id}", $updateData, $this->user);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'nombre' => 'Categoría Actualizada',
                'slug' => 'categoria-actualizada',
            ]);

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'nombre' => 'Categoría Actualizada',
        ]);
    }

    /**
     * Test: No puede actualizar categorías de otra empresa
     */
    public function test_no_puede_actualizar_categorias_de_otra_empresa(): void
    {
        $otraEmpresa = Empresa::factory()->create();
        $categoria = Categoria::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $updateData = ['nombre' => 'Intento de actualización'];

        $response = $this->authenticatedJson('PUT', "/api/categorias/{$categoria->id}", $updateData, $this->user);

        $response->assertStatus(404);
    }

    // ==========================================
    // TESTS: DELETE - Eliminar Categoría (Soft Delete)
    // ==========================================

    /**
     * Test: Puede eliminar categoría (soft delete)
     */
    public function test_puede_eliminar_categoria_soft_delete(): void
    {
        $categoria = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->authenticatedJson('DELETE', "/api/categorias/{$categoria->id}", [], $this->user);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente.',
            ]);

        // Verificar que se eliminó (soft delete)
        $this->assertSoftDeleted('categorias', ['id' => $categoria->id]);
    }

    /**
     * Test: No puede eliminar categorías de otra empresa
     */
    public function test_no_puede_eliminar_categorias_de_otra_empresa(): void
    {
        $otraEmpresa = Empresa::factory()->create();
        $categoria = Categoria::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $response = $this->authenticatedJson('DELETE', "/api/categorias/{$categoria->id}", [], $this->user);

        $response->assertStatus(404);
    }

    // ==========================================
    // TESTS: RESTORE - Restaurar Categoría
    // ==========================================

    /**
     * Test: Puede restaurar categoría eliminada
     */
    public function test_puede_restaurar_categoria_eliminada(): void
    {
        $categoria = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);
        $categoria->delete(); // Soft delete

        $response = $this->authenticatedJson('POST', "/api/categorias/{$categoria->id}/restore", [], $this->user);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Categoría restaurada exitosamente.',
            ]);

        // Verificar que se restauró
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'deleted_at' => null,
        ]);
    }

    // ==========================================
    // TESTS: PRODUCTOS - Listar Productos de Categoría
    // ==========================================

    /**
     * Test: Puede listar productos de una categoría
     */
    public function test_puede_listar_productos_de_categoria(): void
    {
        $categoria = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);

        // Crear productos y asignar a la categoría
        $producto1 = Producto::factory()->create(['empresa_id' => $this->empresa->id]);
        $producto2 = Producto::factory()->create(['empresa_id' => $this->empresa->id]);

        $categoria->productos()->attach([$producto1->id, $producto2->id]);

        $response = $this->authenticatedJson('GET', "/api/categorias/{$categoria->id}/productos", [], $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'nombre',
                        'sku',
                        'precio_venta',
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test: Solo muestra productos activos de la categoría
     */
    public function test_solo_muestra_productos_activos_de_categoria(): void
    {
        $categoria = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);

        // Crear producto activo
        $productoActivo = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);

        // Crear producto inactivo
        $productoInactivo = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'activo' => false,
        ]);

        $categoria->productos()->attach([$productoActivo->id, $productoInactivo->id]);

        $response = $this->authenticatedJson('GET', "/api/categorias/{$categoria->id}/productos", [], $this->user);

        $response->assertStatus(200);

        // Debe devolver solo 1 producto (el activo)
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($productoActivo->id, $response->json('data.0.id'));
    }

    // ==========================================
    // TESTS: MÉTODOS DE UTILIDAD DEL MODELO
    // ==========================================

    /**
     * Test: Método contarProductos funciona correctamente
     */
    public function test_metodo_contar_productos_funciona(): void
    {
        $categoria = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);

        $producto1 = Producto::factory()->create(['empresa_id' => $this->empresa->id]);
        $producto2 = Producto::factory()->create(['empresa_id' => $this->empresa->id]);
        $producto3 = Producto::factory()->create(['empresa_id' => $this->empresa->id]);

        $categoria->productos()->attach([$producto1->id, $producto2->id, $producto3->id]);

        $this->assertEquals(3, $categoria->contarProductos());
    }

    /**
     * Test: Método productosActivos funciona correctamente
     */
    public function test_metodo_productos_activos_funciona(): void
    {
        $categoria = Categoria::factory()->create(['empresa_id' => $this->empresa->id]);

        $productoActivo1 = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $productoActivo2 = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'activo' => true,
        ]);
        $productoInactivo = Producto::factory()->create([
            'empresa_id' => $this->empresa->id,
            'activo' => false,
        ]);

        $categoria->productos()->attach([
            $productoActivo1->id,
            $productoActivo2->id,
            $productoInactivo->id,
        ]);

        $productosActivos = $categoria->productosActivos()->get();

        $this->assertCount(2, $productosActivos);
    }

    /**
     * Test: Slug se actualiza automáticamente al cambiar nombre
     */
    public function test_slug_se_actualiza_al_cambiar_nombre(): void
    {
        $categoria = Categoria::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Nombre Original',
        ]);

        $categoria->update(['nombre' => 'Nombre Actualizado']);

        $this->assertEquals('nombre-actualizado', $categoria->fresh()->slug);
    }
}
