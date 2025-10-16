<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DetallePedido\StoreDetallePedidoRequest;
use App\Http\Requests\DetallePedido\UpdateDetallePedidoRequest;
use App\Http\Resources\DetallePedidoResource;
use App\Models\DetallePedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetallePedidoController extends Controller
{
    /**
     * Listar detalles de pedidos con paginación y búsqueda
     * Búsqueda por: pedido_id, inventario_id
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $pedidoId = $request->input('pedido_id');
        $inventarioId = $request->input('inventario_id');

        $detalles = DetallePedido::query()
            ->with(['pedido', 'inventario'])
            ->when($pedidoId, function ($query, $pedidoId) {
                $query->where('pedido_id', $pedidoId);
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
                      })
                      ->orWhereHas('pedido', function ($subQ) use ($search) {
                          $subQ->where('codigo', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => DetallePedidoResource::collection($detalles),
            'meta' => [
                'current_page' => $detalles->currentPage(),
                'last_page' => $detalles->lastPage(),
                'per_page' => $detalles->perPage(),
                'total' => $detalles->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo detalle de pedido
     */
    public function store(StoreDetallePedidoRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Validar que el pedido pertenezca a la empresa del usuario (multi-tenancy)
            $pedido = \App\Models\Pedido::findOrFail($data['pedido_id']);

            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $pedido->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para agregar detalles a este pedido',
                ], Response::HTTP_FORBIDDEN);
            }

            // Calcular subtotal si no viene en el request
            if (!isset($data['subtotal'])) {
                $data['subtotal'] = ($data['cantidad'] * $data['precio_unitario']) - ($data['descuento'] ?? 0);
            }

            $detalle = DetallePedido::create($data);

            // Cargar relaciones
            $detalle->load(['pedido', 'inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de pedido creado exitosamente',
                'data' => new DetallePedidoResource($detalle),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el detalle de pedido',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar un detalle de pedido específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $detalle = DetallePedido::with(['pedido', 'inventario'])
                ->findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->pedido->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'success' => true,
                'data' => new DetallePedidoResource($detalle),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Detalle de pedido no encontrado',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Actualizar detalle de pedido
     */
    public function update(UpdateDetallePedidoRequest $request, int $id): JsonResponse
    {
        try {
            $detalle = DetallePedido::findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->pedido->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();

            // Si se cambió el pedido, validar que pertenezca a la empresa
            if (isset($data['pedido_id']) && $data['pedido_id'] !== $detalle->pedido_id) {
                $pedido = \App\Models\Pedido::findOrFail($data['pedido_id']);

                if (!$user->hasRole('SuperAdmin') && $pedido->empresa_id !== $user->empresa_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para mover este detalle a ese pedido',
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
            $detalle->load(['pedido', 'inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Detalle de pedido actualizado exitosamente',
                'data' => new DetallePedidoResource($detalle),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el detalle de pedido',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar un detalle de pedido
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $detalle = DetallePedido::findOrFail($id);

            // Validar acceso multi-tenancy
            $user = auth()->user();
            if (!$user->hasRole('SuperAdmin') && $detalle->pedido->empresa_id !== $user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este detalle',
                ], Response::HTTP_FORBIDDEN);
            }

            $detalle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Detalle de pedido eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el detalle de pedido',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar múltiples detalles de pedido (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:detalle_pedidos,id',
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
                $detalles = DetallePedido::with('pedido')
                    ->whereIn('id', $ids)
                    ->get();

                foreach ($detalles as $detalle) {
                    if ($detalle->pedido->empresa_id !== $user->empresa_id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permiso para eliminar uno o más detalles',
                        ], Response::HTTP_FORBIDDEN);
                    }
                }
            }

            $deleted = DetallePedido::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deleted} detalle(s) de pedido exitosamente",
                'deleted_count' => $deleted,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los detalles de pedido',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
