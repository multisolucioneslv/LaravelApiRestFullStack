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
            'currency_id' => $this->currency_id,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
            'favicon' => $this->favicon ? asset('storage/' . $this->favicon) : null,
            'fondo_login' => $this->fondo_login ? asset('storage/' . $this->fondo_login) : null,
            'zona_horaria' => $this->zona_horaria,
            'horarios' => $this->horarios,
            'show_loading_effect' => (bool) $this->show_loading_effect,
            'activo' => (bool) $this->activo,

            // Relaciones
            'phone' => $this->whenLoaded('phone', function () {
                return [
                    'id' => $this->phone->id,
                    'telefono' => $this->phone->telefono,
                ];
            }),
            'currency' => $this->whenLoaded('currency', function () {
                return [
                    'id' => $this->currency->id,
                    'codigo' => $this->currency->codigo,
                    'nombre' => $this->currency->nombre,
                    'simbolo' => $this->currency->simbolo,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
