<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bodega\StoreBodegaRequest;
use App\Http\Requests\Bodega\UpdateBodegaRequest;
use App\Http\Resources\BodegaResource;
use App\Models\Bodega;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BodegaController extends Controller
{
    /**
     * Listar bodegas con paginación y búsqueda
     * Búsqueda por: nombre, codigo
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $bodegas = Bodega::query()
            ->with(['empresa'])
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
            'data' => BodegaResource::collection($bodegas),
            'meta' => [
                'current_page' => $bodegas->currentPage(),
                'last_page' => $bodegas->lastPage(),
                'per_page' => $bodegas->perPage(),
                'total' => $bodegas->total(),
            ],
        ]);
    }

    /**
     * Crear nueva bodega
     */
    public function store(StoreBodegaRequest $request): JsonResponse
    {
        $data = [
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'direccion' => $request->direccion,
            'empresa_id' => $request->empresa_id,
            'activo' => $request->input('activo', true),
        ];

        $bodega = Bodega::create($data);
        $bodega->load(['empresa']);

        return response()->json([
            'success' => true,
            'message' => 'Bodega creada exitosamente',
            'data' => new BodegaResource($bodega),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar una bodega específica
     */
    public function show(int $id): JsonResponse
    {
        $bodega = Bodega::with(['empresa'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new BodegaResource($bodega),
        ]);
    }

    /**
     * Actualizar bodega
     */
    public function update(UpdateBodegaRequest $request, int $id): JsonResponse
    {
        $bodega = Bodega::findOrFail($id);

        $data = [
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'direccion' => $request->direccion,
            'empresa_id' => $request->empresa_id,
            'activo' => $request->activo,
        ];

        $bodega->update($data);
        $bodega->load(['empresa']);

        return response()->json([
            'success' => true,
            'message' => 'Bodega actualizada exitosamente',
            'data' => new BodegaResource($bodega),
        ]);
    }

    /**
     * Eliminar una bodega
     */
    public function destroy(int $id): JsonResponse
    {
        $bodega = Bodega::findOrFail($id);
        $bodega->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bodega eliminada exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples bodegas (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:bodegas,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Bodega::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} bodega(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
