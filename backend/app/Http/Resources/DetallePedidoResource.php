<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetallePedidoResource extends JsonResource
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
            'pedido_id' => $this->pedido_id,
            'inventario_id' => $this->inventario_id,
            'cantidad' => (int) $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'descuento' => (float) $this->descuento,
            'subtotal' => (float) $this->subtotal,

            // Relación con pedido (solo datos básicos)
            'pedido' => $this->whenLoaded('pedido', function () {
                return [
                    'id' => $this->pedido->id,
                    'codigo' => $this->pedido->codigo,
                    'fecha' => $this->pedido->fecha?->format('Y-m-d'),
                    'fecha_entrega' => $this->pedido->fecha_entrega?->format('Y-m-d'),
                    'tipo' => $this->pedido->tipo,
                    'estado' => $this->pedido->estado,
                ];
            }),

            // Relación con inventario
            'inventario' => $this->whenLoaded('inventario', function () {
                return [
                    'id' => $this->inventario->id,
                    'nombre' => $this->inventario->nombre,
                    'codigo' => $this->inventario->codigo,
                    'descripcion' => $this->inventario->descripcion,
                    'precio_compra' => (float) $this->inventario->precio_compra,
                    'precio_venta' => (float) $this->inventario->precio_venta,
                    'cantidad' => (int) $this->inventario->cantidad,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
