<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
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
            'slug' => $this->slug,
            'icono' => $this->icono,
            'color' => $this->color,
            'activo' => (bool) $this->activo,
            'empresa_id' => $this->empresa_id,

            // Datos calculados
            'total_productos' => $this->when(
                isset($this->productos_count),
                $this->productos_count
            ),

            // Relaciones
            'empresa' => $this->whenLoaded('empresa', function () {
                return [
                    'id' => $this->empresa->id,
                    'nombre' => $this->empresa->nombre,
                ];
            }),

            'productos' => $this->whenLoaded('productos', function () {
                return ProductoResource::collection($this->productos);
            }),

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
