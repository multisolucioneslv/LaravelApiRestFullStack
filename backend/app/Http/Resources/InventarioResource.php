<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioResource extends JsonResource
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
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            'galeria_id' => $this->galeria_id,
            'bodega_id' => $this->bodega_id,
            'empresa_id' => $this->empresa_id,
            'cantidad' => (int) $this->cantidad,
            'minimo' => (int) $this->minimo,
            'maximo' => (int) $this->maximo,
            'precio_compra' => (float) $this->precio_compra,
            'precio_venta' => (float) $this->precio_venta,
            'activo' => (bool) $this->activo,

            // Relaciones
            'galeria' => $this->whenLoaded('galeria', function () {
                return $this->galeria ? [
                    'id' => $this->galeria->id,
                    'nombre' => $this->galeria->nombre,
                ] : null;
            }),
            'bodega' => $this->whenLoaded('bodega', function () {
                return [
                    'id' => $this->bodega->id,
                    'nombre' => $this->bodega->nombre,
                ];
            }),
            'empresa' => $this->whenLoaded('empresa', function () {
                return [
                    'id' => $this->empresa->id,
                    'nombre' => $this->empresa->nombre,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
