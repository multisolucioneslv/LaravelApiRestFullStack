<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoriaController extends Controller
{
    /**
     * Listar categorías con paginación y búsqueda
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $categorias = Categoria::query()
            ->forCurrentUser() // Multi-tenancy
            ->with(['empresa'])
            ->withCount('productos')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $categorias->items(),
            'meta' => [
                'current_page' => $categorias->currentPage(),
                'last_page' => $categorias->lastPage(),
                'per_page' => $categorias->perPage(),
                'total' => $categorias->total(),
            ],
        ]);
    }

    /**
     * Crear nueva categoría
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:categorias,slug',
            'icono' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre de la categoría es requerido',
            'slug.unique' => 'Este slug ya está registrado',
        ]);

        $data = [
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'slug' => $validated['slug'] ?? null, // Si es null, se genera automáticamente en el modelo
            'icono' => $validated['icono'] ?? null,
            'color' => $validated['color'] ?? null,
            'activo' => $validated['activo'] ?? true,
            'empresa_id' => auth()->user()->empresa_id, // Multi-tenancy
        ];

        $categoria = Categoria::create($data);
        $categoria->load('empresa');

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => $categoria,
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar una categoría específica
     */
    public function show(int $id): JsonResponse
    {
        $categoria = Categoria::with(['empresa', 'productos'])
            ->withCount('productos')
            ->findOrFail($id);

        // Multi-tenancy
        $categoria->validateEmpresaAccess($categoria->empresa_id);

        return response()->json([
            'success' => true,
            'data' => $categoria,
        ]);
    }

    /**
     * Actualizar categoría
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $categoria = Categoria::findOrFail($id);

        // Multi-tenancy
        $categoria->validateEmpresaAccess($categoria->empresa_id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'slug' => 'nullable|string|max:255|unique:categorias,slug,' . $id,
            'icono' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'activo' => 'boolean',
        ]);

        $data = [
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'slug' => $validated['slug'] ?? $categoria->slug,
            'icono' => $validated['icono'] ?? $categoria->icono,
            'color' => $validated['color'] ?? $categoria->color,
            'activo' => $validated['activo'] ?? $categoria->activo,
        ];

        $categoria->update($data);
        $categoria->load('empresa');

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data' => $categoria,
        ]);
    }

    /**
     * Eliminar una categoría (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        $categoria = Categoria::findOrFail($id);

        // Multi-tenancy
        $categoria->validateEmpresaAccess($categoria->empresa_id);

        // Verificar si tiene productos asociados
        if ($categoria->productos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene productos asociados',
            ], Response::HTTP_CONFLICT);
        }

        $categoria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente',
        ]);
    }

    /**
     * Restaurar una categoría eliminada
     */
    public function restore(int $id): JsonResponse
    {
        $categoria = Categoria::withTrashed()->findOrFail($id);

        // Multi-tenancy
        $categoria->validateEmpresaAccess($categoria->empresa_id);

        $categoria->restore();

        return response()->json([
            'success' => true,
            'message' => 'Categoría restaurada exitosamente',
            'data' => $categoria,
        ]);
    }
}
