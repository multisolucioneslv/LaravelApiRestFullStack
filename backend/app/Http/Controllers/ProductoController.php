<?php

namespace App\Http\Controllers;

use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use App\Http\Requests\Producto\UpdateStockRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ProductoController extends Controller
{
    /**
     * Constructor - Aplicar middleware de permisos
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:productos.index')->only(['index']);
        $this->middleware('permission:productos.show')->only(['show']);
        $this->middleware('permission:productos.store')->only(['store']);
        $this->middleware('permission:productos.update')->only(['update']);
        $this->middleware('permission:productos.destroy')->only(['destroy']);
        $this->middleware('permission:productos.restore')->only(['restore']);
        $this->middleware('permission:productos.stock')->only(['updateStock']);
    }

    /**
     * Listar productos con paginación, búsqueda y filtros
     * Filtros: nombre, sku, categoria, activo, bajo_stock
     * Búsqueda: nombre, sku, codigo_barras
     */
    public function index(Request $request): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('viewAny', Producto::class);

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $productos = Producto::query()
            ->with(['empresa', 'categorias', 'inventarios'])
            ->withCount('inventarios')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('codigo_barras', 'like', "%{$search}%");
                });
            })
            // Filtro por categoría
            ->when($request->has('categoria_id'), function ($query) use ($request) {
                $query->whereHas('categorias', function ($q) use ($request) {
                    $q->where('categorias.id', $request->categoria_id);
                });
            })
            // Filtro por activo
            ->when($request->has('activo'), function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            // Filtro por productos con bajo stock
            ->when($request->boolean('bajo_stock'), function ($query) {
                $query->whereColumn('stock_actual', '<', 'stock_minimo');
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ProductoResource::collection($productos),
            'meta' => [
                'current_page' => $productos->currentPage(),
                'last_page' => $productos->lastPage(),
                'per_page' => $productos->perPage(),
                'total' => $productos->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Crear nuevo producto
     */
    public function store(StoreProductoRequest $request): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('create', Producto::class);

        try {
            // Obtener empresa_id del usuario autenticado
            $empresaId = auth()->user()->empresa_id;

            $data = $request->validated();
            $data['empresa_id'] = $empresaId;
            $data['activo'] = $data['activo'] ?? true;

            // Crear producto
            $producto = Producto::create($data);

            // Sincronizar categorías si se enviaron
            if (!empty($data['categorias'])) {
                $producto->categorias()->sync($data['categorias']);
            }

            // Cargar relaciones
            $producto->load(['empresa', 'categorias', 'inventarios']);

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => new ProductoResource($producto),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar un producto específico con sus relaciones
     */
    public function show(Producto $producto): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('view', $producto);

        // Cargar relaciones
        $producto->load(['empresa', 'categorias', 'inventarios.bodega']);
        $producto->loadCount('inventarios');

        return response()->json([
            'success' => true,
            'data' => new ProductoResource($producto),
        ], Response::HTTP_OK);
    }

    /**
     * Actualizar producto
     */
    public function update(UpdateProductoRequest $request, Producto $producto): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('update', $producto);

        try {
            $data = $request->validated();

            // Actualizar producto
            $producto->update($data);

            // Sincronizar categorías si se enviaron
            if (isset($data['categorias'])) {
                $producto->categorias()->sync($data['categorias']);
            }

            // Cargar relaciones
            $producto->load(['empresa', 'categorias', 'inventarios']);

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'data' => new ProductoResource($producto),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar producto (soft delete)
     */
    public function destroy(Producto $producto): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('delete', $producto);

        try {
            $producto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restaurar producto eliminado
     */
    public function restore(int $id): JsonResponse
    {
        $producto = Producto::withTrashed()->findOrFail($id);

        // Autorizar mediante Policy
        Gate::authorize('restore', $producto);

        try {
            $producto->restore();

            return response()->json([
                'success' => true,
                'message' => 'Producto restaurado exitosamente',
                'data' => new ProductoResource($producto->fresh(['empresa', 'categorias'])),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el producto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Actualizar stock del producto
     */
    public function updateStock(UpdateStockRequest $request, Producto $producto): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('updateStock', $producto);

        try {
            $cantidad = $request->validated()['cantidad'];
            $tipo = $request->validated()['tipo'];

            // Usar el método del modelo
            $producto->actualizarStock($cantidad, $tipo);

            return response()->json([
                'success' => true,
                'message' => 'Stock actualizado exitosamente',
                'data' => new ProductoResource($producto->fresh(['empresa', 'categorias'])),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el stock',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
