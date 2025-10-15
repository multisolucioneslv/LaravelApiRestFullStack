<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Moneda\StoreMonedaRequest;
use App\Http\Requests\Moneda\UpdateMonedaRequest;
use App\Http\Resources\MonedaResource;
use App\Models\Moneda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MonedaController extends Controller
{
    /**
     * Listar monedas con paginación y búsqueda
     * Búsqueda por: codigo, nombre, simbolo
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $monedas = Moneda::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                      ->orWhere('nombre', 'like', "%{$search}%")
                      ->orWhere('simbolo', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => MonedaResource::collection($monedas),
            'meta' => [
                'current_page' => $monedas->currentPage(),
                'last_page' => $monedas->lastPage(),
                'per_page' => $monedas->perPage(),
                'total' => $monedas->total(),
            ],
        ]);
    }

    /**
     * Crear nueva moneda
     */
    public function store(StoreMonedaRequest $request): JsonResponse
    {
        $data = [
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'simbolo' => $request->simbolo,
            'tasa_cambio' => $request->tasa_cambio,
            'activo' => $request->input('activo', true),
        ];

        $moneda = Moneda::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Moneda creada exitosamente',
            'data' => new MonedaResource($moneda),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar una moneda específica
     */
    public function show(int $id): JsonResponse
    {
        $moneda = Moneda::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new MonedaResource($moneda),
        ]);
    }

    /**
     * Actualizar moneda
     */
    public function update(UpdateMonedaRequest $request, int $id): JsonResponse
    {
        $moneda = Moneda::findOrFail($id);

        $data = [
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'simbolo' => $request->simbolo,
            'tasa_cambio' => $request->tasa_cambio,
            'activo' => $request->activo,
        ];

        $moneda->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Moneda actualizada exitosamente',
            'data' => new MonedaResource($moneda),
        ]);
    }

    /**
     * Eliminar una moneda
     */
    public function destroy(int $id): JsonResponse
    {
        $moneda = Moneda::findOrFail($id);
        $moneda->delete();

        return response()->json([
            'success' => true,
            'message' => 'Moneda eliminada exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples monedas (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:monedas,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Moneda::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} moneda(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
