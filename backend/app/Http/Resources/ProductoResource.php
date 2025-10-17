<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'sku' => $this->sku,
            'codigo_barras' => $this->codigo_barras,
            'precio_compra' => floatval($this->precio_compra),
            'precio_venta' => floatval($this->precio_venta),
            'precio_mayoreo' => $this->precio_mayoreo ? floatval($this->precio_mayoreo) : null,
            'stock_minimo' => $this->stock_minimo,
            'stock_actual' => $this->stock_actual,
            'unidad_medida' => $this->unidad_medida,
            'imagen' => $this->imagen,
            'activo' => (bool) $this->activo,
            'empresa_id' => $this->empresa_id,

            // Estados calculados
            'bajo_stock' => $this->esBajoStock(),
            'disponible' => $this->estaDisponible(),
            'margen_ganancia' => round($this->calcularMargenGanancia(), 2),

            // Relaciones
            'empresa' => $this->whenLoaded('empresa', function () {
                return [
                    'id' => $this->empresa->id,
                    'nombre' => $this->empresa->nombre,
                ];
            }),

            'categorias' => $this->whenLoaded('categorias', function () {
                return CategoriaResource::collection($this->categorias);
            }),

            'inventarios' => $this->whenLoaded('inventarios', function () {
                return InventarioResource::collection($this->inventarios);
            }),

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
