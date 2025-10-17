<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
     * Display a listing of the resource.
     */
    public function index()
    {
        // Autorizar mediante Policy
        Gate::authorize('viewAny', Producto::class);

        // TODO: Implementar listado de productos
        return response()->json([
            'message' => 'Listado de productos (pendiente de implementar)'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Autorizar mediante Policy
        Gate::authorize('create', Producto::class);

        // TODO: Implementar creación de producto
        return response()->json([
            'message' => 'Crear producto (pendiente de implementar)'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        // Autorizar mediante Policy
        Gate::authorize('view', $producto);

        // TODO: Implementar detalle de producto
        return response()->json([
            'message' => 'Detalle de producto (pendiente de implementar)',
            'producto' => $producto
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        // Autorizar mediante Policy
        Gate::authorize('update', $producto);

        // TODO: Implementar actualización de producto
        return response()->json([
            'message' => 'Actualizar producto (pendiente de implementar)'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        // Autorizar mediante Policy
        Gate::authorize('delete', $producto);

        // TODO: Implementar eliminación (soft delete) de producto
        return response()->json([
            'message' => 'Eliminar producto (pendiente de implementar)'
        ]);
    }

    /**
     * Restore the specified resource from trash.
     */
    public function restore($id)
    {
        $producto = Producto::withTrashed()->findOrFail($id);

        // Autorizar mediante Policy
        Gate::authorize('restore', $producto);

        // TODO: Implementar restauración de producto
        return response()->json([
            'message' => 'Restaurar producto (pendiente de implementar)'
        ]);
    }

    /**
     * Update stock of the specified product.
     */
    public function updateStock(Request $request, Producto $producto)
    {
        // Autorizar mediante Policy
        Gate::authorize('updateStock', $producto);

        // TODO: Implementar actualización de stock
        return response()->json([
            'message' => 'Actualizar stock (pendiente de implementar)'
        ]);
    }
}
