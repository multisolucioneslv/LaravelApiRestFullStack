<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bodega;
use App\Models\Categoria;
use App\Models\Cotizacion;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    /**
     * Obtener todas las estadísticas del dashboard
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            // Obtener empresa_id del usuario autenticado
            $empresaId = auth()->user()->empresa_id;

            // Fechas para cálculos
            $hoy = Carbon::today();
            $inicioMes = Carbon::now()->startOfMonth();
            $finMes = Carbon::now()->endOfMonth();
            $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
            $finMesAnterior = Carbon::now()->subMonth()->endOfMonth();

            // ==========================================
            // MÉTRICAS DE VENTAS
            // ==========================================
            $ventasData = $this->getVentasMetrics($empresaId, $hoy, $inicioMes, $finMes, $inicioMesAnterior, $finMesAnterior);

            // ==========================================
            // MÉTRICAS DE PRODUCTOS
            // ==========================================
            $productosData = $this->getProductosMetrics($empresaId);

            // ==========================================
            // MÉTRICAS DE COTIZACIONES
            // ==========================================
            $cotizacionesData = $this->getCotizacionesMetrics($empresaId, $inicioMes, $finMes);

            // ==========================================
            // MÉTRICAS DE PEDIDOS
            // ==========================================
            $pedidosData = $this->getPedidosMetrics($empresaId, $hoy, $inicioMes, $finMes);

            // ==========================================
            // MÉTRICAS DE USUARIOS
            // ==========================================
            $usuariosData = $this->getUsuariosMetrics($empresaId, $inicioMes, $finMes);

            // ==========================================
            // MÉTRICAS DE INVENTARIO
            // ==========================================
            $inventarioData = $this->getInventarioMetrics($empresaId);

            // ==========================================
            // MÉTRICAS DE CATEGORÍAS
            // ==========================================
            $categoriasData = $this->getCategoriasMetrics($empresaId);

            return response()->json([
                'success' => true,
                'data' => [
                    'ventas' => $ventasData,
                    'productos' => $productosData,
                    'cotizaciones' => $cotizacionesData,
                    'pedidos' => $pedidosData,
                    'usuarios' => $usuariosData,
                    'inventario' => $inventarioData,
                    'categorias' => $categoriasData,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del dashboard',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obtener métricas de ventas
     *
     * @param int $empresaId
     * @param Carbon $hoy
     * @param Carbon $inicioMes
     * @param Carbon $finMes
     * @param Carbon $inicioMesAnterior
     * @param Carbon $finMesAnterior
     * @return array
     */
    private function getVentasMetrics(
        int $empresaId,
        Carbon $hoy,
        Carbon $inicioMes,
        Carbon $finMes,
        Carbon $inicioMesAnterior,
        Carbon $finMesAnterior
    ): array {
        // Total de ventas y total vendido
        $ventasTotal = Venta::where('empresa_id', $empresaId)->count();
        $totalVendido = Venta::where('empresa_id', $empresaId)->sum('total') ?? 0;

        // Ventas del mes actual
        $ventasMesActual = Venta::where('empresa_id', $empresaId)
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->sum('total') ?? 0;

        // Ventas del día
        $ventasDia = Venta::where('empresa_id', $empresaId)
            ->whereDate('fecha', $hoy)
            ->sum('total') ?? 0;

        // Ventas del mes anterior
        $ventasMesAnterior = Venta::where('empresa_id', $empresaId)
            ->whereBetween('fecha', [$inicioMesAnterior, $finMesAnterior])
            ->sum('total') ?? 0;

        // Calcular crecimiento del mes (evitar división por cero)
        $crecimientoMes = 0;
        if ($ventasMesAnterior > 0) {
            $crecimientoMes = (($ventasMesActual - $ventasMesAnterior) / $ventasMesAnterior) * 100;
        }

        // Top 5 ventas más grandes
        $topVentas = Venta::where('empresa_id', $empresaId)
            ->select('id', 'codigo', 'fecha', 'total', 'estado')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($venta) {
                return [
                    'id' => $venta->id,
                    'codigo' => $venta->codigo,
                    'fecha' => $venta->fecha->format('Y-m-d'),
                    'total' => floatval($venta->total),
                    'estado' => $venta->estado,
                ];
            });

        return [
            'total' => $ventasTotal,
            'total_vendido' => floatval($totalVendido),
            'mes_actual' => floatval($ventasMesActual),
            'dia_actual' => floatval($ventasDia),
            'crecimiento_mes' => round($crecimientoMes, 2),
            'top_ventas' => $topVentas,
        ];
    }

    /**
     * Obtener métricas de productos
     *
     * @param int $empresaId
     * @return array
     */
    private function getProductosMetrics(int $empresaId): array
    {
        // Total de productos
        $total = Producto::where('empresa_id', $empresaId)->count();

        // Productos activos vs inactivos
        $activos = Producto::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->count();

        $inactivos = $total - $activos;

        // Productos con bajo stock (stock_actual < stock_minimo)
        $bajoStock = Producto::where('empresa_id', $empresaId)
            ->whereRaw('stock_actual < stock_minimo')
            ->count();

        // Productos sin stock (stock_actual = 0)
        $sinStock = Producto::where('empresa_id', $empresaId)
            ->where('stock_actual', 0)
            ->count();

        // Valor total del inventario (precio_venta * stock_actual)
        $valorInventario = Producto::where('empresa_id', $empresaId)
            ->selectRaw('SUM(precio_venta * stock_actual) as valor_total')
            ->value('valor_total') ?? 0;

        // Top 10 productos más vendidos
        $topProductos = DetalleVenta::join('inventarios', 'detalle_ventas.inventario_id', '=', 'inventarios.id')
            ->join('productos', 'inventarios.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->where('productos.empresa_id', $empresaId)
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.sku',
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_ventas.subtotal) as total_ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.sku')
            ->orderBy('total_vendido', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'sku' => $producto->sku,
                    'total_vendido' => intval($producto->total_vendido),
                    'total_ingresos' => floatval($producto->total_ingresos),
                ];
            });

        return [
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $inactivos,
            'bajo_stock' => $bajoStock,
            'sin_stock' => $sinStock,
            'valor_inventario' => floatval($valorInventario),
            'top_productos' => $topProductos,
        ];
    }

    /**
     * Obtener métricas de cotizaciones
     *
     * @param int $empresaId
     * @param Carbon $inicioMes
     * @param Carbon $finMes
     * @return array
     */
    private function getCotizacionesMetrics(int $empresaId, Carbon $inicioMes, Carbon $finMes): array
    {
        // Total de cotizaciones
        $total = Cotizacion::where('empresa_id', $empresaId)->count();

        // Cotizaciones por estado
        $pendientes = Cotizacion::where('empresa_id', $empresaId)
            ->where('estado', 'pendiente')
            ->count();

        $aprobadas = Cotizacion::where('empresa_id', $empresaId)
            ->where('estado', 'aprobada')
            ->count();

        $rechazadas = Cotizacion::where('empresa_id', $empresaId)
            ->where('estado', 'rechazada')
            ->count();

        // Tasa de conversión (evitar división por cero)
        $tasaConversion = 0;
        if ($total > 0) {
            $tasaConversion = ($aprobadas / $total) * 100;
        }

        // Cotizaciones del mes
        $mesActual = Cotizacion::where('empresa_id', $empresaId)
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->count();

        return [
            'total' => $total,
            'pendientes' => $pendientes,
            'aprobadas' => $aprobadas,
            'rechazadas' => $rechazadas,
            'tasa_conversion' => round($tasaConversion, 2),
            'mes_actual' => $mesActual,
        ];
    }

    /**
     * Obtener métricas de pedidos
     *
     * @param int $empresaId
     * @param Carbon $hoy
     * @param Carbon $inicioMes
     * @param Carbon $finMes
     * @return array
     */
    private function getPedidosMetrics(int $empresaId, Carbon $hoy, Carbon $inicioMes, Carbon $finMes): array
    {
        // Total de pedidos
        $total = Pedido::where('empresa_id', $empresaId)->count();

        // Pedidos por estado
        $pendiente = Pedido::where('empresa_id', $empresaId)
            ->where('estado', 'pendiente')
            ->count();

        $procesando = Pedido::where('empresa_id', $empresaId)
            ->where('estado', 'procesando')
            ->count();

        $enviado = Pedido::where('empresa_id', $empresaId)
            ->where('estado', 'enviado')
            ->count();

        $entregado = Pedido::where('empresa_id', $empresaId)
            ->where('estado', 'entregado')
            ->count();

        $cancelado = Pedido::where('empresa_id', $empresaId)
            ->where('estado', 'cancelado')
            ->count();

        // Pedidos del día
        $diaActual = Pedido::where('empresa_id', $empresaId)
            ->whereDate('fecha', $hoy)
            ->count();

        // Pedidos del mes
        $mesActual = Pedido::where('empresa_id', $empresaId)
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->count();

        return [
            'total' => $total,
            'pendiente' => $pendiente,
            'procesando' => $procesando,
            'enviado' => $enviado,
            'entregado' => $entregado,
            'cancelado' => $cancelado,
            'dia_actual' => $diaActual,
            'mes_actual' => $mesActual,
        ];
    }

    /**
     * Obtener métricas de usuarios
     *
     * @param int $empresaId
     * @param Carbon $inicioMes
     * @param Carbon $finMes
     * @return array
     */
    private function getUsuariosMetrics(int $empresaId, Carbon $inicioMes, Carbon $finMes): array
    {
        // Total de usuarios
        $total = User::where('empresa_id', $empresaId)->count();

        // Usuarios activos vs inactivos
        $activos = User::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->count();

        $inactivos = $total - $activos;

        // Usuarios que han comprado (usuarios con al menos una venta)
        $conCompras = User::where('empresa_id', $empresaId)
            ->whereHas('ventas')
            ->count();

        // Nuevos usuarios del mes
        $nuevosMes = User::where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->count();

        // Usuarios por rol
        $porRol = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('users.empresa_id', $empresaId)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->select('roles.name as rol', DB::raw('COUNT(*) as total'))
            ->groupBy('roles.name')
            ->get()
            ->map(function ($item) {
                return [
                    'rol' => $item->rol,
                    'total' => intval($item->total),
                ];
            });

        return [
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $inactivos,
            'con_compras' => $conCompras,
            'nuevos_mes' => $nuevosMes,
            'por_rol' => $porRol,
        ];
    }

    /**
     * Obtener métricas de inventario
     *
     * @param int $empresaId
     * @return array
     */
    private function getInventarioMetrics(int $empresaId): array
    {
        // Total de bodegas
        $totalBodegas = Bodega::where('empresa_id', $empresaId)->count();

        // Inventario por bodega
        $porBodega = Bodega::where('empresa_id', $empresaId)
            ->with(['inventarios' => function ($query) {
                $query->select('bodega_id', DB::raw('COUNT(*) as total_productos'), DB::raw('SUM(cantidad * precio_venta) as valor_total'));
            }])
            ->get()
            ->map(function ($bodega) {
                $totalProductos = Inventario::where('bodega_id', $bodega->id)->count();
                $valorTotal = Inventario::where('bodega_id', $bodega->id)
                    ->selectRaw('SUM(cantidad * precio_venta) as valor')
                    ->value('valor') ?? 0;

                return [
                    'id' => $bodega->id,
                    'nombre' => $bodega->nombre,
                    'codigo' => $bodega->codigo,
                    'total_productos' => $totalProductos,
                    'valor_total' => floatval($valorTotal),
                ];
            });

        return [
            'total_bodegas' => $totalBodegas,
            'por_bodega' => $porBodega,
        ];
    }

    /**
     * Obtener métricas de categorías
     *
     * @param int $empresaId
     * @return array
     */
    private function getCategoriasMetrics(int $empresaId): array
    {
        // Total de categorías
        $total = Categoria::where('empresa_id', $empresaId)->count();

        // Top 5 categorías con más productos
        $topCategorias = Categoria::where('empresa_id', $empresaId)
            ->withCount('productos')
            ->orderBy('productos_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($categoria) {
                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                    'total_productos' => $categoria->productos_count,
                ];
            });

        return [
            'total' => $total,
            'top_categorias' => $topCategorias,
        ];
    }
}
