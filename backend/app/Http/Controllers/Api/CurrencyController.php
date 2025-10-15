<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Currency\StoreCurrencyRequest;
use App\Http\Requests\Currency\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrencyController extends Controller
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

        $currencies = Currency::query()
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
            'data' => CurrencyResource::collection($currencies),
            'meta' => [
                'current_page' => $currencies->currentPage(),
                'last_page' => $currencies->lastPage(),
                'per_page' => $currencies->perPage(),
                'total' => $currencies->total(),
            ],
        ]);
    }

    /**
     * Crear nueva moneda
     */
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $data = [
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'simbolo' => $request->simbolo,
            'tasa_cambio' => $request->tasa_cambio,
            'activo' => $request->input('activo', true),
        ];

        $currency = Currency::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Moneda creada exitosamente',
            'data' => new CurrencyResource($currency),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar una moneda específica
     */
    public function show(int $id): JsonResponse
    {
        $currency = Currency::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new CurrencyResource($currency),
        ]);
    }

    /**
     * Actualizar moneda
     */
    public function update(UpdateCurrencyRequest $request, int $id): JsonResponse
    {
        $currency = Currency::findOrFail($id);

        $data = [
            'codigo' => $request->codigo,
            'nombre' => $request->nombre,
            'simbolo' => $request->simbolo,
            'tasa_cambio' => $request->tasa_cambio,
            'activo' => $request->activo,
        ];

        $currency->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Moneda actualizada exitosamente',
            'data' => new CurrencyResource($currency),
        ]);
    }

    /**
     * Eliminar una moneda
     */
    public function destroy(int $id): JsonResponse
    {
        $currency = Currency::findOrFail($id);
        $currency->delete();

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
            'ids.*' => 'required|integer|exists:currencies,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Currency::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} moneda(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
