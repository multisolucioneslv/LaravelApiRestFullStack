<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleVentaResource extends JsonResource
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
            'venta_id' => $this->venta_id,
            'inventario_id' => $this->inventario_id,
            'cantidad' => (int) $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'descuento' => (float) $this->descuento,
            'subtotal' => (float) $this->subtotal,

            // Relación con venta (solo datos básicos)
            'venta' => $this->whenLoaded('venta', function () {
                return [
                    'id' => $this->venta->id,
                    'codigo' => $this->venta->codigo,
                    'fecha' => $this->venta->fecha?->format('Y-m-d'),
                    'estado' => $this->venta->estado,
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
