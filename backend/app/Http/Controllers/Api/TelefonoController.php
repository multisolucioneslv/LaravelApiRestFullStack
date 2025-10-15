<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Telefono\StoreTelefonoRequest;
use App\Http\Requests\Telefono\UpdateTelefonoRequest;
use App\Http\Resources\TelefonoResource;
use App\Models\Telefono;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TelefonoController extends Controller
{
    /**
     * Listar teléfonos con paginación y búsqueda
     * Búsqueda por: telefono
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $telefonos = Telefono::query()
            ->when($search, function ($query, $search) {
                $query->where('telefono', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TelefonoResource::collection($telefonos),
            'meta' => [
                'current_page' => $telefonos->currentPage(),
                'last_page' => $telefonos->lastPage(),
                'per_page' => $telefonos->perPage(),
                'total' => $telefonos->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo teléfono
     */
    public function store(StoreTelefonoRequest $request): JsonResponse
    {
        $telefono = Telefono::create([
            'telefono' => $request->telefono,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Teléfono creado exitosamente',
            'data' => new TelefonoResource($telefono),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un teléfono específico
     */
    public function show(int $id): JsonResponse
    {
        $telefono = Telefono::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new TelefonoResource($telefono),
        ]);
    }

    /**
     * Actualizar teléfono
     */
    public function update(UpdateTelefonoRequest $request, int $id): JsonResponse
    {
        $telefono = Telefono::findOrFail($id);

        $telefono->update([
            'telefono' => $request->telefono,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Teléfono actualizado exitosamente',
            'data' => new TelefonoResource($telefono),
        ]);
    }

    /**
     * Eliminar un teléfono
     */
    public function destroy(int $id): JsonResponse
    {
        $telefono = Telefono::findOrFail($id);
        $telefono->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teléfono eliminado exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples teléfonos (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:telefonos,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Telefono::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} teléfono(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
