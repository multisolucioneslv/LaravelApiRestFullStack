<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DetalleVenta\StoreDetalleVentaRequest;
use App\Http\Requests\DetalleVenta\UpdateDetalleVentaRequest;
use App\Http\Resources\DetalleVentaResource;
use App\Models\DetalleVenta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetalleVentaController extends Controller
{
    /**
     * Listar detalles de ventas con paginación y búsqueda
     * Búsqueda por: venta_id, inventario_id
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $ventaId = $request->input('venta_id');
        $inventarioId = $request->input('inventario_id');

        $detalles = DetalleVenta::query()
            ->with(['venta', 'inventario'])
            ->when($ventaId, function ($query, $ventaId) {
                $query->where('venta_id', $ventaId);
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
            'data' => DetalleVentaResource::collection($detalles),
            'meta' => [
                'current_page' => $detalles->currentPage(),
                'last_page' => $detalles->lastPage(),
                'per_page' => $detalles->perPage(),
                'total' => $detalles->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo detalle de venta
     */
    public function store(StoreDetalleVentaRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Validar que la venta pertenezca a la empresa del usuario (multi-tenancy)
            $venta = \App\Models\Venta::findOrFail($data['venta_id']);

            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $venta->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para agregar detalles a esta venta',
                ], Response::HTTP_FORBIDDEN);
            }

            // Calcular subtotal si no viene en el request
            if (!isset($data['subtotal'])) {
                $data['subtotal'] = ($data['cantidad'] * $data['precio_unitario']) - ($data['descuento'] ?? 0);
            }

            $detalle = DetalleVenta::create($data);

            // Cargar relaciones
            $detalle->load(['venta', 'inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de venta creado exitosamente',
                'data' => new DetalleVentaResource($detalle),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el detalle de venta',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar un detalle de venta específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $detalle = DetalleVenta::with(['venta', 'inventario'])
                ->findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->venta->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'success' => true,
                'data' => new DetalleVentaResource($detalle),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Detalle de venta no encontrado',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Actualizar detalle de venta
     */
    public function update(UpdateDetalleVentaRequest $request, int $id): JsonResponse
    {
        try {
            $detalle = DetalleVenta::findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->venta->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();

            // Si se cambió la venta, validar que pertenezca a la empresa
            if (isset($data['venta_id']) && $data['venta_id'] !== $detalle->venta_id) {
                $venta = \App\Models\Venta::findOrFail($data['venta_id']);

                if (!$user->hasRole('SuperAdmin') && $venta->empresa_id !== $user->empresa_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para mover este detalle a esa venta',
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
            $detalle->load(['venta', 'inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de venta actualizado exitosamente',
                'data' => new DetalleVentaResource($detalle),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el detalle de venta',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar un detalle de venta
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $detalle = DetalleVenta::findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->venta->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            $detalle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Detalle de venta eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el detalle de venta',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar múltiples detalles de venta (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:detalle_ventas,id',
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
                $detalles = DetalleVenta::with('venta')
                    ->whereIn('id', $ids)
                    ->get();

                foreach ($detalles as $detalle) {
                    if ($detalle->venta->empresa_id !== $user->empresa_id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permiso para eliminar uno o más detalles',
                        ], Response::HTTP_FORBIDDEN);
                    }
                }
            }

            $deleted = DetalleVenta::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deleted} detalle(s) de venta exitosamente",
                'deleted_count' => $deleted,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los detalles de venta',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
