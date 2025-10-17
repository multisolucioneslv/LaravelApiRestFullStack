<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoriaController extends Controller
{
    /**
     * Constructor - Aplicar middleware de permisos
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:categorias.index')->only(['index']);
        $this->middleware('permission:categorias.show')->only(['show']);
        $this->middleware('permission:categorias.store')->only(['store']);
        $this->middleware('permission:categorias.update')->only(['update']);
        $this->middleware('permission:categorias.destroy')->only(['destroy']);
        $this->middleware('permission:categorias.restore')->only(['restore']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Autorizar mediante Policy
        Gate::authorize('viewAny', Categoria::class);

        // TODO: Implementar listado de categorías
        return response()->json([
            'message' => 'Listado de categorías (pendiente de implementar)'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Autorizar mediante Policy
        Gate::authorize('create', Categoria::class);

        // TODO: Implementar creación de categoría
        return response()->json([
            'message' => 'Crear categoría (pendiente de implementar)'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Categoria $categoria)
    {
        // Autorizar mediante Policy
        Gate::authorize('view', $categoria);

        // TODO: Implementar detalle de categoría
        return response()->json([
            'message' => 'Detalle de categoría (pendiente de implementar)',
            'categoria' => $categoria
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoria $categoria)
    {
        // Autorizar mediante Policy
        Gate::authorize('update', $categoria);

        // TODO: Implementar actualización de categoría
        return response()->json([
            'message' => 'Actualizar categoría (pendiente de implementar)'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        // Autorizar mediante Policy
        Gate::authorize('delete', $categoria);

        // TODO: Implementar eliminación (soft delete) de categoría
        return response()->json([
            'message' => 'Eliminar categoría (pendiente de implementar)'
        ]);
    }

    /**
     * Restore the specified resource from trash.
     */
    public function restore($id)
    {
        $categoria = Categoria::withTrashed()->findOrFail($id);

        // Autorizar mediante Policy
        Gate::authorize('restore', $categoria);

        // TODO: Implementar restauración de categoría
        return response()->json([
            'message' => 'Restaurar categoría (pendiente de implementar)'
        ]);
    }
}
