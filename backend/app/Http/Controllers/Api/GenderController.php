<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gender\StoreGenderRequest;
use App\Http\Requests\Gender\UpdateGenderRequest;
use App\Http\Resources\GenderResource;
use App\Models\Gender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GenderController extends Controller
{
    /**
     * Listar géneros con paginación y búsqueda
     * Búsqueda por: sexo, inicial
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $genders = Gender::query()
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
            'data' => GenderResource::collection($genders),
            'meta' => [
                'current_page' => $genders->currentPage(),
                'last_page' => $genders->lastPage(),
                'per_page' => $genders->perPage(),
                'total' => $genders->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo género
     */
    public function store(StoreGenderRequest $request): JsonResponse
    {
        $gender = Gender::create([
            'sexo' => $request->sexo,
            'inicial' => $request->inicial,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Género creado exitosamente',
            'data' => new GenderResource($gender),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un género específico
     */
    public function show(int $id): JsonResponse
    {
        $gender = Gender::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new GenderResource($gender),
        ]);
    }

    /**
     * Actualizar género
     */
    public function update(UpdateGenderRequest $request, int $id): JsonResponse
    {
        $gender = Gender::findOrFail($id);

        $gender->update([
            'sexo' => $request->sexo,
            'inicial' => $request->inicial,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Género actualizado exitosamente',
            'data' => new GenderResource($gender),
        ]);
    }

    /**
     * Eliminar un género
     */
    public function destroy(int $id): JsonResponse
    {
        $gender = Gender::findOrFail($id);
        $gender->delete();

        return response()->json([
            'success' => true,
            'message' => 'Género eliminado exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples géneros (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:genders,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Gender::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} género(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
