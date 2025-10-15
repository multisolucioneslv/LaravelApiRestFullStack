<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
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
            'telefono_id' => $this->telefono_id,
            'moneda_id' => $this->moneda_id,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
            'favicon' => $this->favicon ? asset('storage/' . $this->favicon) : null,
            'fondo_login' => $this->fondo_login ? asset('storage/' . $this->fondo_login) : null,
            'zona_horaria' => $this->zona_horaria,
            'activo' => (bool) $this->activo,

            // Relaciones
            'telefono' => $this->whenLoaded('telefono', function () {
                return [
                    'id' => $this->telefono->id,
                    'telefono' => $this->telefono->telefono,
                ];
            }),
            'moneda' => $this->whenLoaded('moneda', function () {
                return [
                    'id' => $this->moneda->id,
                    'nombre' => $this->moneda->nombre,
                    'simbolo' => $this->moneda->simbolo,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
