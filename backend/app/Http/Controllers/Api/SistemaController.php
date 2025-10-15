<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sistema\StoreSistemaRequest;
use App\Http\Requests\Sistema\UpdateSistemaRequest;
use App\Http\Resources\SistemaResource;
use App\Models\Sistema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SistemaController extends Controller
{
    /**
     * Listar sistemas con paginación y búsqueda
     * Búsqueda por: nombre, version
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $sistemas = Sistema::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('version', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SistemaResource::collection($sistemas),
            'meta' => [
                'current_page' => $sistemas->currentPage(),
                'last_page' => $sistemas->lastPage(),
                'per_page' => $sistemas->perPage(),
                'total' => $sistemas->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo sistema
     */
    public function store(StoreSistemaRequest $request): JsonResponse
    {
        $data = [
            'nombre' => $request->nombre,
            'version' => $request->input('version', '1.0.0'),
            'configuracion' => $request->configuracion,
            'activo' => $request->input('activo', true),
        ];

        // Manejar upload de logo
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $extension = $logo->getClientOriginalExtension();

            // Guardar con el nombre del sistema + microtime para evitar caché
            $filename = str_replace(' ', '_', $request->nombre) . '.' . microtime(true) . '.' . $extension;
            $path = $logo->storeAs('logos', $filename, 'public');
            $data['logo'] = $path;
        }

        $sistema = Sistema::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Sistema creado exitosamente',
            'data' => new SistemaResource($sistema),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un sistema específico
     */
    public function show(int $id): JsonResponse
    {
        $sistema = Sistema::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new SistemaResource($sistema),
        ]);
    }

    /**
     * Actualizar sistema
     */
    public function update(UpdateSistemaRequest $request, int $id): JsonResponse
    {
        $sistema = Sistema::findOrFail($id);

        $data = [
            'nombre' => $request->nombre,
            'version' => $request->version,
            'configuracion' => $request->configuracion,
            'activo' => $request->activo,
        ];

        // Manejar upload de logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($sistema->logo && Storage::disk('public')->exists($sistema->logo)) {
                Storage::disk('public')->delete($sistema->logo);
            }

            $logo = $request->file('logo');
            $extension = $logo->getClientOriginalExtension();

            // Guardar con el nombre del sistema + microtime para evitar caché
            $filename = str_replace(' ', '_', $request->nombre) . '.' . microtime(true) . '.' . $extension;
            $path = $logo->storeAs('logos', $filename, 'public');
            $data['logo'] = $path;
        }

        $sistema->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Sistema actualizado exitosamente',
            'data' => new SistemaResource($sistema),
        ]);
    }

    /**
     * Eliminar un sistema
     */
    public function destroy(int $id): JsonResponse
    {
        $sistema = Sistema::findOrFail($id);
        $sistema->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sistema eliminado exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples sistemas (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:sistemas,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Sistema::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} sistema(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
