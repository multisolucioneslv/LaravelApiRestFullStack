<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\StoreSettingRequest;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    /**
     * Listar settings con paginación y búsqueda
     * Búsqueda por: nombre, clave
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $settings = Setting::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('clave', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SettingResource::collection($settings),
            'meta' => [
                'current_page' => $settings->currentPage(),
                'last_page' => $settings->lastPage(),
                'per_page' => $settings->perPage(),
                'total' => $settings->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo setting
     */
    public function store(StoreSettingRequest $request): JsonResponse
    {
        $data = [
            'nombre' => $request->nombre,
            'accion' => $request->input('accion', true),
            'clave' => $request->clave,
        ];

        $setting = Setting::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración creada exitosamente',
            'data' => new SettingResource($setting),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un setting específico
     */
    public function show(int $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new SettingResource($setting),
        ]);
    }

    /**
     * Actualizar setting
     */
    public function update(UpdateSettingRequest $request, int $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);

        $data = [
            'nombre' => $request->nombre,
            'accion' => $request->accion,
            'clave' => $request->clave,
        ];

        $setting->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada exitosamente',
            'data' => new SettingResource($setting),
        ]);
    }

    /**
     * Eliminar un setting
     */
    public function destroy(int $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Configuración eliminada exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples settings (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:settings,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Setting::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} configuración(es) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
