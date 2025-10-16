<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DetalleCotizacion\StoreDetalleCotizacionRequest;
use App\Http\Requests\DetalleCotizacion\UpdateDetalleCotizacionRequest;
use App\Http\Resources\DetalleCotizacionResource;
use App\Models\DetalleCotizacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetalleCotizacionController extends Controller
{
    /**
     * Listar detalles de cotizaciones con paginación y búsqueda
     * Búsqueda por: cotizacion_id, inventario_id
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $cotizacionId = $request->input('cotizacion_id');
        $inventarioId = $request->input('inventario_id');

        $detalles = DetalleCotizacion::query()
            ->with(['cotizacion', 'inventario'])
            ->when($cotizacionId, function ($query, $cotizacionId) {
                $query->where('cotizacion_id', $cotizacionId);
            })
            ->when($inventarioId, function ($query, $inventarioId) {
                $query->where('inventario_id', $inventarioId);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('cantidad', 'like', "%{$search}%")
                      ->orWhere('precio_unitario', 'like', "%{$search}%")
                      ->orWhereHas('inventario', function ($subQ) use ($search) {
                          $subQ->where('nombre', 'like', "%{$search}%")
                               ->orWhere('codigo', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => DetalleCotizacionResource::collection($detalles),
            'meta' => [
                'current_page' => $detalles->currentPage(),
                'last_page' => $detalles->lastPage(),
                'per_page' => $detalles->perPage(),
                'total' => $detalles->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo detalle de cotización
     */
    public function store(StoreDetalleCotizacionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Validar que la cotización pertenezca a la empresa del usuario (multi-tenancy)
            $cotizacion = \App\Models\Cotizacion::findOrFail($data['cotizacion_id']);

            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $cotizacion->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para agregar detalles a esta cotización',
                ], Response::HTTP_FORBIDDEN);
            }

            // Calcular subtotal si no viene en el request
            if (!isset($data['subtotal'])) {
                $data['subtotal'] = ($data['cantidad'] * $data['precio_unitario']) - ($data['descuento'] ?? 0);
            }

            $detalle = DetalleCotizacion::create($data);

            // Cargar relaciones
            $detalle->load(['cotizacion', 'inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de cotización creado exitosamente',
                'data' => new DetalleCotizacionResource($detalle),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el detalle de cotización',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar un detalle de cotización específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $detalle = DetalleCotizacion::with(['cotizacion', 'inventario'])
                ->findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->cotizacion->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'success' => true,
                'data' => new DetalleCotizacionResource($detalle),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Detalle de cotización no encontrado',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Actualizar detalle de cotización
     */
    public function update(UpdateDetalleCotizacionRequest $request, int $id): JsonResponse
    {
        try {
            $detalle = DetalleCotizacion::findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->cotizacion->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();

            // Si se cambió la cotización, validar que pertenezca a la empresa
            if (isset($data['cotizacion_id']) && $data['cotizacion_id'] !== $detalle->cotizacion_id) {
                $cotizacion = \App\Models\Cotizacion::findOrFail($data['cotizacion_id']);

                if (!$user->hasRole('SuperAdmin') && $cotizacion->empresa_id !== $user->empresa_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para mover este detalle a esa cotización',
                    ], Response::HTTP_FORBIDDEN);
                }
            }

            // Recalcular subtotal
            if (!isset($data['subtotal'])) {
                $cantidad = $data['cantidad'] ?? $detalle->cantidad;
                $precioUnitario = $data['precio_unitario'] ?? $detalle->precio_unitario;
                $descuento = $data['descuento'] ?? $detalle->descuento;

                $data['subtotal'] = ($cantidad * $precioUnitario) - $descuento;
            }

            $detalle->update($data);

            // Cargar relaciones
            $detalle->load(['cotizacion', 'inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de cotización actualizado exitosamente',
                'data' => new DetalleCotizacionResource($detalle),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el detalle de cotización',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar un detalle de cotización
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $detalle = DetalleCotizacion::findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->cotizacion->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            $detalle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Detalle de cotización eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el detalle de cotización',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar múltiples detalles de cotización (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:detalle_cotizaciones,id',
        ], [
            'ids.required' => 'Debe proporcionar al menos un ID para eliminar',
            'ids.array' => 'Los IDs deben ser un arreglo',
            'ids.min' => 'Debe proporcionar al menos un ID para eliminar',
            'ids.*.required' => 'Cada ID es requerido',
            'ids.*.integer' => 'Los IDs deben ser números enteros',
            'ids.*.exists' => 'Uno o más detalles no existen',
        ]);

        try {
            $ids = $request->input('ids');

            // Validar acceso multi-tenancy para cada detalle
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin')) {
                $detalles = DetalleCotizacion::with('cotizacion')
                    ->whereIn('id', $ids)
                    ->get();

                foreach ($detalles as $detalle) {
                    if ($detalle->cotizacion->empresa_id !== $user->empresa_id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permiso para eliminar uno o más detalles',
                        ], Response::HTTP_FORBIDDEN);
                    }
                }
            }

            $deleted = DetalleCotizacion::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deleted} detalle(s) de cotización exitosamente",
                'deleted_count' => $deleted,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los detalles de cotización',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
