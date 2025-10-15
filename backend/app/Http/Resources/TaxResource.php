<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxResource extends JsonResource
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
            'porcentaje' => (float) $this->porcentaje,
            'descripcion' => $this->descripcion,
            'empresa_id' => $this->empresa_id,
            'empresa' => $this->when($this->relationLoaded('empresa'), [
                'id' => $this->empresa?->id,
                'nombre' => $this->empresa?->nombre,
            ]),
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
