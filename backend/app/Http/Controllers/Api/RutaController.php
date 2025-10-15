<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ruta\StoreRutaRequest;
use App\Http\Requests\Ruta\UpdateRutaRequest;
use App\Http\Resources\RutaResource;
use App\Models\Ruta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RutaController extends Controller
{
    /**
     * Listar rutas API con paginación y búsqueda
     * Búsqueda por: ruta, metodo, descripcion, controlador, accion
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $rutas = Ruta::query()
            ->with('sistema') // Cargar relación con sistema
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ruta', 'like', "%{$search}%")
                      ->orWhere('metodo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%")
                      ->orWhere('controlador', 'like', "%{$search}%")
                      ->orWhere('accion', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => RutaResource::collection($rutas),
            'meta' => [
                'current_page' => $rutas->currentPage(),
                'last_page' => $rutas->lastPage(),
                'per_page' => $rutas->perPage(),
                'total' => $rutas->total(),
            ],
        ]);
    }

    /**
     * Crear nueva ruta API
     */
    public function store(StoreRutaRequest $request): JsonResponse
    {
        $data = [
            'sistema_id' => $request->sistema_id,
            'ruta' => $request->ruta,
            'metodo' => $request->metodo,
            'descripcion' => $request->descripcion,
            'controlador' => $request->controlador,
            'accion' => $request->accion,
            'middleware' => $request->middleware,
            'activo' => $request->input('activo', true),
        ];

        $ruta = Ruta::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Ruta API creada exitosamente',
            'data' => new RutaResource($ruta->load('sistema')),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar una ruta API específica
     */
    public function show(int $id): JsonResponse
    {
        $ruta = Ruta::with('sistema')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new RutaResource($ruta),
        ]);
    }

    /**
     * Actualizar ruta API
     */
    public function update(UpdateRutaRequest $request, int $id): JsonResponse
    {
        $ruta = Ruta::findOrFail($id);

        $data = [
            'sistema_id' => $request->sistema_id,
            'ruta' => $request->ruta,
            'metodo' => $request->metodo,
            'descripcion' => $request->descripcion,
            'controlador' => $request->controlador,
            'accion' => $request->accion,
            'middleware' => $request->middleware,
            'activo' => $request->activo,
        ];

        $ruta->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Ruta API actualizada exitosamente',
            'data' => new RutaResource($ruta->load('sistema')),
        ]);
    }

    /**
     * Eliminar una ruta API
     */
    public function destroy(int $id): JsonResponse
    {
        $ruta = Ruta::findOrFail($id);
        $ruta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ruta API eliminada exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples rutas API (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:rutas,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Ruta::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} ruta(s) API exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
