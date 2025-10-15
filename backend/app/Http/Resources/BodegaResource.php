<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BodegaResource extends JsonResource
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
            'direccion' => $this->direccion,
            'activo' => (bool) $this->activo,
            'empresa_id' => $this->empresa_id,

            // Relaciones
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
