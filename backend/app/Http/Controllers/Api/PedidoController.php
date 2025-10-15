<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pedido\StorePedidoRequest;
use App\Http\Requests\Pedido\UpdatePedidoRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PedidoController extends Controller
{
    /**
     * Listar todos los pedidos con paginación y búsqueda
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $perPage = $request->input('per_page', 15);

        $query = Pedido::with(['empresa', 'user']);

        // Búsqueda por código, tipo o estado
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%")
                  ->orWhere('estado', 'like', "%{$search}%");
            });
        }

        $pedidos = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => PedidoResource::collection($pedidos->items()),
            'meta' => [
                'current_page' => $pedidos->currentPage(),
                'last_page' => $pedidos->lastPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
            ]
        ]);
    }

    /**
     * Crear un nuevo pedido
     *
     * @param StorePedidoRequest $request
     * @return JsonResponse
     */
    public function store(StorePedidoRequest $request): JsonResponse
    {
        try {
            // Generar código automático
            $codigo = $this->generarCodigoPedido();

            $pedidoData = array_merge($request->validated(), [
                'codigo' => $codigo,
                'user_id' => auth()->id(),
            ]);

            $pedido = Pedido::create($pedidoData);
            $pedido->load(['empresa', 'user']);

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'data' => new PedidoResource($pedido)
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el pedido',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar un pedido específico
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $pedido = Pedido::with(['empresa', 'user'])->find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new PedidoResource($pedido)
        ]);
    }

    /**
     * Actualizar un pedido existente
     *
     * @param UpdatePedidoRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdatePedidoRequest $request, int $id): JsonResponse
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $pedido->update($request->validated());
            $pedido->load(['empresa', 'user']);

            return response()->json([
                'message' => 'Pedido actualizado exitosamente',
                'data' => new PedidoResource($pedido)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el pedido',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar un pedido (soft delete)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json([
                'message' => 'Pedido no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $pedido->delete();

            return response()->json([
                'message' => 'Pedido eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el pedido',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar múltiples pedidos (soft delete)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pedidos,id'
        ]);

        try {
            Pedido::whereIn('id', $request->ids)->delete();

            return response()->json([
                'message' => 'Pedidos eliminados exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar los pedidos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generar código único para el pedido
     * Formato: PED-YYYY-NNNN
     *
     * @return string
     */
    private function generarCodigoPedido(): string
    {
        $year = now()->year;
        $prefix = "PED-{$year}-";

        // Obtener el último código del año actual
        $ultimoPedido = Pedido::where('codigo', 'like', "{$prefix}%")
            ->orderBy('codigo', 'desc')
            ->first();

        if ($ultimoPedido) {
            // Extraer el número del último código
            $ultimoNumero = (int) substr($ultimoPedido->codigo, -4);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }

        // Formatear con 4 dígitos
        return $prefix . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }
}
