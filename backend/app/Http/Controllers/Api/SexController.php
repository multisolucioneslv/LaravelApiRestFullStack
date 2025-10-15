<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sex\StoreSexRequest;
use App\Http\Requests\Sex\UpdateSexRequest;
use App\Http\Resources\SexResource;
use App\Models\Sex;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SexController extends Controller
{
    /**
     * Listar sexos con paginación y búsqueda
     * Búsqueda por: sexo, inicial
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $sexes = Sex::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('sexo', 'like', "%{$search}%")
                      ->orWhere('inicial', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SexResource::collection($sexes),
            'meta' => [
                'current_page' => $sexes->currentPage(),
                'last_page' => $sexes->lastPage(),
                'per_page' => $sexes->perPage(),
                'total' => $sexes->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo sexo
     */
    public function store(StoreSexRequest $request): JsonResponse
    {
        $sex = Sex::create([
            'sexo' => $request->sexo,
            'inicial' => $request->inicial,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sexo creado exitosamente',
            'data' => new SexResource($sex),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un sexo específico
     */
    public function show(int $id): JsonResponse
    {
        $sex = Sex::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new SexResource($sex),
        ]);
    }

    /**
     * Actualizar sexo
     */
    public function update(UpdateSexRequest $request, int $id): JsonResponse
    {
        $sex = Sex::findOrFail($id);

        $sex->update([
            'sexo' => $request->sexo,
            'inicial' => $request->inicial,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sexo actualizado exitosamente',
            'data' => new SexResource($sex),
        ]);
    }

    /**
     * Eliminar un sexo
     */
    public function destroy(int $id): JsonResponse
    {
        $sex = Sex::findOrFail($id);
        $sex->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sexo eliminado exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples sexos (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:sexes,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Sex::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} sexo(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
