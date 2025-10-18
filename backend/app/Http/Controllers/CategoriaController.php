<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categoria\StoreCategoriaRequest;
use App\Http\Requests\Categoria\UpdateCategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Http\Resources\ProductoResource;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CategoriaController extends Controller
{
    /**
     * Constructor - Aplicar middleware de permisos
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:categorias.index')->only(['index', 'all']);
        $this->middleware('permission:categorias.show')->only(['show', 'productosDeCategoria']);
        $this->middleware('permission:categorias.store')->only(['store']);
        $this->middleware('permission:categorias.update')->only(['update']);
        $this->middleware('permission:categorias.destroy')->only(['destroy']);
        $this->middleware('permission:categorias.restore')->only(['restore']);
    }

    /**
     * Listar TODAS las categorías (sin paginación) para selectores/dropdowns
     */
    public function all(Request $request): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('viewAny', Categoria::class);

        $categorias = Categoria::query()
            ->where('activo', true)
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoriaResource::collection($categorias),
        ], Response::HTTP_OK);
    }

    /**
     * Listar categorías con paginación, búsqueda y filtros
     * Filtros: nombre, slug, activo
     * Búsqueda: nombre, slug
     */
    public function index(Request $request): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('viewAny', Categoria::class);

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $categorias = Categoria::query()
            ->with(['empresa'])
            ->withCount('productos')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            // Filtro por activo
            ->when($request->has('activo'), function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CategoriaResource::collection($categorias),
            'meta' => [
                'current_page' => $categorias->currentPage(),
                'last_page' => $categorias->lastPage(),
                'per_page' => $categorias->perPage(),
                'total' => $categorias->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Crear nueva categoría
     */
    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('create', Categoria::class);

        try {
            // Obtener empresa_id del usuario autenticado
            $empresaId = auth()->user()->empresa_id;

            $data = $request->validated();
            $data['empresa_id'] = $empresaId;
            $data['activo'] = $data['activo'] ?? true;

            // Crear categoría (el slug se genera automáticamente en el modelo)
            $categoria = Categoria::create($data);

            // Cargar relaciones
            $categoria->load(['empresa']);

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => new CategoriaResource($categoria),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la categoría',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar una categoría específica con sus relaciones
     */
    public function show(Categoria $categoria): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('view', $categoria);

        // Cargar relaciones
        $categoria->load(['empresa', 'productos']);
        $categoria->loadCount('productos');

        return response()->json([
            'success' => true,
            'data' => new CategoriaResource($categoria),
        ], Response::HTTP_OK);
    }

    /**
     * Actualizar categoría
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('update', $categoria);

        try {
            $data = $request->validated();

            // Actualizar categoría
            $categoria->update($data);

            // Cargar relaciones
            $categoria->load(['empresa']);

            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'data' => new CategoriaResource($categoria),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la categoría',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar categoría (soft delete)
     */
    public function destroy(Categoria $categoria): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('delete', $categoria);

        try {
            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la categoría',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restaurar categoría eliminada
     */
    public function restore(int $id): JsonResponse
    {
        $categoria = Categoria::withTrashed()->findOrFail($id);

        // Autorizar mediante Policy
        Gate::authorize('restore', $categoria);

        try {
            $categoria->restore();

            return response()->json([
                'success' => true,
                'message' => 'Categoría restaurada exitosamente',
                'data' => new CategoriaResource($categoria->fresh(['empresa'])),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar la categoría',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Listar productos de una categoría específica
     */
    public function productosDeCategoria(Categoria $categoria, Request $request): JsonResponse
    {
        // Autorizar mediante Policy
        Gate::authorize('view', $categoria);

        $perPage = $request->input('per_page', 15);

        $productos = $categoria->productos()
            ->with(['empresa', 'categorias'])
            ->when($request->has('activo'), function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'categoria' => new CategoriaResource($categoria),
            'data' => ProductoResource::collection($productos),
            'meta' => [
                'current_page' => $productos->currentPage(),
                'last_page' => $productos->lastPage(),
                'per_page' => $productos->perPage(),
                'total' => $productos->total(),
            ],
        ], Response::HTTP_OK);
    }
}
