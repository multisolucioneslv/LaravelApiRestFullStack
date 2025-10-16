<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Venta\StoreVentaRequest;
use App\Http\Requests\Venta\UpdateVentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VentaController extends Controller
{
    /**
     * Listar ventas con paginación y búsqueda
     * Búsqueda por: codigo, estado, tipo_pago
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $ventas = Venta::query()
            ->with(['empresa', 'user', 'cotizacion', 'currency', 'tax', 'detalles.inventario'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                      ->orWhere('estado', 'like', "%{$search}%")
                      ->orWhere('tipo_pago', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => VentaResource::collection($ventas),
            'meta' => [
                'current_page' => $ventas->currentPage(),
                'last_page' => $ventas->lastPage(),
                'per_page' => $ventas->perPage(),
                'total' => $ventas->total(),
            ],
        ]);
    }

    /**
     * Crear nueva venta con sus detalles
     * Genera código automáticamente: VEN-{año}-{número secuencial}
     */
    public function store(StoreVentaRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Generar código automático
            $codigo = $this->generarCodigoVenta();

            // Obtener el tax para calcular el impuesto
            $tax = null;
            $porcentajeImpuesto = 0;
            if ($request->tax_id) {
                $tax = Tax::find($request->tax_id);
                $porcentajeImpuesto = $tax ? $tax->porcentaje : 0;
            }

            // Calcular totales desde los detalles
            $totales = $this->calcularTotales($request->detalles, $porcentajeImpuesto);

            // Crear la venta
            $data = [
                'codigo' => $codigo,
                'fecha' => $request->fecha,
                'estado' => $request->input('estado', 'pendiente'),
                'tipo_pago' => $request->tipo_pago,
                'observaciones' => $request->observaciones,
                'empresa_id' => $request->empresa_id,
                'user_id' => auth()->id(), // Usuario autenticado
                'cotizacion_id' => $request->cotizacion_id,
                'currency_id' => $request->currency_id,
                'tax_id' => $request->tax_id,
                'subtotal' => $totales['subtotal'],
                'impuesto' => $totales['impuesto'],
                'descuento' => $totales['descuento'],
                'total' => $totales['total'],
            ];

            $venta = Venta::create($data);

            // Crear los detalles
            foreach ($request->detalles as $detalle) {
                $subtotalDetalle = ($detalle['cantidad'] * $detalle['precio_unitario']) - ($detalle['descuento'] ?? 0);

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'inventario_id' => $detalle['inventario_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento' => $detalle['descuento'] ?? 0,
                    'subtotal' => $subtotalDetalle,
                ]);
            }

            DB::commit();

            // Cargar relaciones
            $venta->load(['empresa', 'user', 'cotizacion', 'currency', 'tax', 'detalles.inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Venta creada exitosamente',
                'data' => new VentaResource($venta),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la venta',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar una venta específica
     */
    public function show(int $id): JsonResponse
    {
        $venta = Venta::with(['empresa', 'user', 'cotizacion', 'currency', 'tax', 'detalles.inventario'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new VentaResource($venta),
        ]);
    }

    /**
     * Actualizar venta
     * Elimina detalles anteriores y crea nuevos
     */
    public function update(UpdateVentaRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $venta = Venta::findOrFail($id);

            // Obtener el tax para calcular el impuesto
            $tax = null;
            $porcentajeImpuesto = 0;
            if ($request->tax_id) {
                $tax = Tax::find($request->tax_id);
                $porcentajeImpuesto = $tax ? $tax->porcentaje : 0;
            }

            // Calcular totales desde los detalles
            $totales = $this->calcularTotales($request->detalles, $porcentajeImpuesto);

            // Actualizar la venta
            $data = [
                'fecha' => $request->fecha,
                'estado' => $request->input('estado', $venta->estado),
                'tipo_pago' => $request->tipo_pago,
                'observaciones' => $request->observaciones,
                'empresa_id' => $request->empresa_id,
                'cotizacion_id' => $request->cotizacion_id,
                'currency_id' => $request->currency_id,
                'tax_id' => $request->tax_id,
                'subtotal' => $totales['subtotal'],
                'impuesto' => $totales['impuesto'],
                'descuento' => $totales['descuento'],
                'total' => $totales['total'],
            ];

            $venta->update($data);

            // Eliminar detalles anteriores
            DetalleVenta::where('venta_id', $venta->id)->delete();

            // Crear nuevos detalles
            foreach ($request->detalles as $detalle) {
                $subtotalDetalle = ($detalle['cantidad'] * $detalle['precio_unitario']) - ($detalle['descuento'] ?? 0);

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'inventario_id' => $detalle['inventario_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento' => $detalle['descuento'] ?? 0,
                    'subtotal' => $subtotalDetalle,
                ]);
            }

            DB::commit();

            // Cargar relaciones
            $venta->load(['empresa', 'user', 'cotizacion', 'currency', 'tax', 'detalles.inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada exitosamente',
                'data' => new VentaResource($venta),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la venta',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar una venta
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $venta = Venta::findOrFail($id);

            // Los detalles se eliminan automáticamente por la cascada en la migración
            $venta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la venta',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar múltiples ventas (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:ventas,id',
        ]);

        try {
            $ids = $request->input('ids');
            $deleted = Venta::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deleted} venta(s) exitosamente",
                'deleted_count' => $deleted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar las ventas',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generar código automático para venta
     * Formato: VEN-{año}-{número secuencial}
     */
    private function generarCodigoVenta(): string
    {
        $anio = date('Y');

        // Obtener el último código del año actual
        $ultimaVenta = Venta::where('codigo', 'like', "VEN-{$anio}-%")
            ->orderBy('codigo', 'desc')
            ->first();

        if ($ultimaVenta) {
            // Extraer el número secuencial y sumarle 1
            $partes = explode('-', $ultimaVenta->codigo);
            $numero = intval($partes[2]) + 1;
        } else {
            // Primera venta del año
            $numero = 1;
        }

        // Formatear con 4 dígitos
        $numeroFormateado = str_pad($numero, 4, '0', STR_PAD_LEFT);

        return "VEN-{$anio}-{$numeroFormateado}";
    }

    /**
     * Calcular totales de la venta
     */
    private function calcularTotales(array $detalles, float $porcentajeImpuesto): array
    {
        $subtotal = 0;
        $descuentoTotal = 0;

        foreach ($detalles as $detalle) {
            $cantidad = $detalle['cantidad'];
            $precioUnitario = $detalle['precio_unitario'];
            $descuento = $detalle['descuento'] ?? 0;

            $subtotalLinea = $cantidad * $precioUnitario;
            $subtotal += $subtotalLinea;
            $descuentoTotal += $descuento;
        }

        // Calcular subtotal después de descuentos
        $subtotalConDescuento = $subtotal - $descuentoTotal;

        // Calcular impuesto
        $impuesto = ($subtotalConDescuento * $porcentajeImpuesto) / 100;

        // Calcular total
        $total = $subtotalConDescuento + $impuesto;

        return [
            'subtotal' => round($subtotal, 2),
            'descuento' => round($descuentoTotal, 2),
            'impuesto' => round($impuesto, 2),
            'total' => round($total, 2),
        ];
    }
}
