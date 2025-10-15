<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetalleCotizacionResource extends JsonResource
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
            'cotizacion_id' => $this->cotizacion_id,
            'inventario_id' => $this->inventario_id,
            'cantidad' => (int) $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'descuento' => (float) $this->descuento,
            'subtotal' => (float) $this->subtotal,

            // Relación con inventario
            'inventario' => $this->whenLoaded('inventario', function () {
                return [
                    'id' => $this->inventario->id,
                    'nombre' => $this->inventario->nombre,
                    'codigo' => $this->inventario->codigo,
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
