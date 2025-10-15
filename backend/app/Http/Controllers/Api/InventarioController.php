<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventario\StoreInventarioRequest;
use App\Http\Requests\Inventario\UpdateInventarioRequest;
use App\Http\Resources\InventarioResource;
use App\Models\Inventario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InventarioController extends Controller
{
    /**
     * Listar inventarios con paginación y búsqueda
     * Búsqueda por: nombre, codigo
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $inventarios = Inventario::query()
            ->with(['galeria', 'bodega', 'empresa'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => InventarioResource::collection($inventarios),
            'meta' => [
                'current_page' => $inventarios->currentPage(),
                'last_page' => $inventarios->lastPage(),
                'per_page' => $inventarios->perPage(),
                'total' => $inventarios->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo inventario
     */
    public function store(StoreInventarioRequest $request): JsonResponse
    {
        $data = [
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'galeria_id' => $request->galeria_id,
            'bodega_id' => $request->bodega_id,
            'empresa_id' => $request->empresa_id,
            'cantidad' => $request->input('cantidad', 0),
            'minimo' => $request->input('minimo', 0),
            'maximo' => $request->input('maximo', 0),
            'precio_compra' => $request->input('precio_compra', 0.00),
            'precio_venta' => $request->input('precio_venta', 0.00),
            'activo' => $request->input('activo', true),
        ];

        $inventario = Inventario::create($data);
        $inventario->load(['galeria', 'bodega', 'empresa']);

        return response()->json([
            'success' => true,
            'message' => 'Inventario creado exitosamente',
            'data' => new InventarioResource($inventario),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un inventario específico
     */
    public function show(int $id): JsonResponse
    {
        $inventario = Inventario::with(['galeria', 'bodega', 'empresa'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new InventarioResource($inventario),
        ]);
    }

    /**
     * Actualizar inventario
     */
    public function update(UpdateInventarioRequest $request, int $id): JsonResponse
    {
        $inventario = Inventario::findOrFail($id);

        $data = [
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'galeria_id' => $request->galeria_id,
            'bodega_id' => $request->bodega_id,
            'empresa_id' => $request->empresa_id,
            'cantidad' => $request->cantidad,
            'minimo' => $request->minimo,
            'maximo' => $request->maximo,
            'precio_compra' => $request->precio_compra,
            'precio_venta' => $request->precio_venta,
            'activo' => $request->activo,
        ];

        $inventario->update($data);
        $inventario->load(['galeria', 'bodega', 'empresa']);

        return response()->json([
            'success' => true,
            'message' => 'Inventario actualizado exitosamente',
            'data' => new InventarioResource($inventario),
        ]);
    }

    /**
     * Eliminar un inventario
     */
    public function destroy(int $id): JsonResponse
    {
        $inventario = Inventario::findOrFail($id);
        $inventario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventario eliminado exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples inventarios (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:inventarios,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Inventario::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} inventario(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
