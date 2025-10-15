<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cotizacion\StoreCotizacionRequest;
use App\Http\Requests\Cotizacion\UpdateCotizacionRequest;
use App\Http\Resources\CotizacionResource;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CotizacionController extends Controller
{
    /**
     * Listar cotizaciones con paginación y búsqueda
     * Búsqueda por: codigo, estado
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $cotizaciones = Cotizacion::query()
            ->with(['empresa', 'user', 'moneda', 'tax', 'detalles.inventario'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                      ->orWhere('estado', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CotizacionResource::collection($cotizaciones),
            'meta' => [
                'current_page' => $cotizaciones->currentPage(),
                'last_page' => $cotizaciones->lastPage(),
                'per_page' => $cotizaciones->perPage(),
                'total' => $cotizaciones->total(),
            ],
        ]);
    }

    /**
     * Crear nueva cotización con sus detalles
     * Genera código automáticamente: COT-{año}-{número secuencial}
     */
    public function store(StoreCotizacionRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Generar código automático
            $codigo = $this->generarCodigoCotizacion();

            // Obtener el tax para calcular el impuesto
            $tax = null;
            $porcentajeImpuesto = 0;
            if ($request->tax_id) {
                $tax = Tax::find($request->tax_id);
                $porcentajeImpuesto = $tax ? $tax->porcentaje : 0;
            }

            // Calcular totales desde los detalles
            $totales = $this->calcularTotales($request->detalles, $porcentajeImpuesto);

            // Crear la cotización
            $data = [
                'codigo' => $codigo,
                'fecha' => $request->fecha,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'estado' => $request->input('estado', 'pendiente'),
                'observaciones' => $request->observaciones,
                'empresa_id' => $request->empresa_id,
                'user_id' => auth()->id(), // Usuario autenticado
                'moneda_id' => $request->moneda_id,
                'tax_id' => $request->tax_id,
                'subtotal' => $totales['subtotal'],
                'impuesto' => $totales['impuesto'],
                'descuento' => $totales['descuento'],
                'total' => $totales['total'],
            ];

            $cotizacion = Cotizacion::create($data);

            // Crear los detalles
            foreach ($request->detalles as $detalle) {
                $subtotalDetalle = ($detalle['cantidad'] * $detalle['precio_unitario']) - ($detalle['descuento'] ?? 0);

                DetalleCotizacion::create([
                    'cotizacion_id' => $cotizacion->id,
                    'inventario_id' => $detalle['inventario_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento' => $detalle['descuento'] ?? 0,
                    'subtotal' => $subtotalDetalle,
                ]);
            }

            DB::commit();

            // Cargar relaciones
            $cotizacion->load(['empresa', 'user', 'moneda', 'tax', 'detalles.inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización creada exitosamente',
                'data' => new CotizacionResource($cotizacion),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cotización',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar una cotización específica
     */
    public function show(int $id): JsonResponse
    {
        $cotizacion = Cotizacion::with(['empresa', 'user', 'moneda', 'tax', 'detalles.inventario'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new CotizacionResource($cotizacion),
        ]);
    }

    /**
     * Actualizar cotización
     * Elimina detalles anteriores y crea nuevos
     */
    public function update(UpdateCotizacionRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $cotizacion = Cotizacion::findOrFail($id);

            // Obtener el tax para calcular el impuesto
            $tax = null;
            $porcentajeImpuesto = 0;
            if ($request->tax_id) {
                $tax = Tax::find($request->tax_id);
                $porcentajeImpuesto = $tax ? $tax->porcentaje : 0;
            }

            // Calcular totales desde los detalles
            $totales = $this->calcularTotales($request->detalles, $porcentajeImpuesto);

            // Actualizar la cotización
            $data = [
                'fecha' => $request->fecha,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'estado' => $request->input('estado', $cotizacion->estado),
                'observaciones' => $request->observaciones,
                'empresa_id' => $request->empresa_id,
                'moneda_id' => $request->moneda_id,
                'tax_id' => $request->tax_id,
                'subtotal' => $totales['subtotal'],
                'impuesto' => $totales['impuesto'],
                'descuento' => $totales['descuento'],
                'total' => $totales['total'],
            ];

            $cotizacion->update($data);

            // Eliminar detalles anteriores
            DetalleCotizacion::where('cotizacion_id', $cotizacion->id)->delete();

            // Crear nuevos detalles
            foreach ($request->detalles as $detalle) {
                $subtotalDetalle = ($detalle['cantidad'] * $detalle['precio_unitario']) - ($detalle['descuento'] ?? 0);

                DetalleCotizacion::create([
                    'cotizacion_id' => $cotizacion->id,
                    'inventario_id' => $detalle['inventario_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento' => $detalle['descuento'] ?? 0,
                    'subtotal' => $subtotalDetalle,
                ]);
            }

            DB::commit();

            // Cargar relaciones
            $cotizacion->load(['empresa', 'user', 'moneda', 'tax', 'detalles.inventario']);

            return response()->json([
                'success' => true,
                'message' => 'Cotización actualizada exitosamente',
                'data' => new CotizacionResource($cotizacion),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cotización',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar una cotización
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $cotizacion = Cotizacion::findOrFail($id);

            // Los detalles se eliminan automáticamente por la cascada en la migración
            $cotizacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cotización eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cotización',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar múltiples cotizaciones (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:cotizaciones,id',
        ]);

        try {
            $ids = $request->input('ids');
            $deleted = Cotizacion::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deleted} cotización(es) exitosamente",
                'deleted_count' => $deleted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar las cotizaciones',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generar código automático para cotización
     * Formato: COT-{año}-{número secuencial}
     */
    private function generarCodigoCotizacion(): string
    {
        $anio = date('Y');

        // Obtener el último código del año actual
        $ultimaCotizacion = Cotizacion::where('codigo', 'like', "COT-{$anio}-%")
            ->orderBy('codigo', 'desc')
            ->first();

        if ($ultimaCotizacion) {
            // Extraer el número secuencial y sumarle 1
            $partes = explode('-', $ultimaCotizacion->codigo);
            $numero = intval($partes[2]) + 1;
        } else {
            // Primera cotización del año
            $numero = 1;
        }

        // Formatear con 4 dígitos
        $numeroFormateado = str_pad($numero, 4, '0', STR_PAD_LEFT);

        return "COT-{$anio}-{$numeroFormateado}";
    }

    /**
     * Calcular totales de la cotización
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
