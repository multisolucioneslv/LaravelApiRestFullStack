<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
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
            'codigo' => $this->codigo,
            'fecha' => $this->fecha?->format('Y-m-d'),
            'fecha_formateada' => $this->fecha?->format('d/m/Y'),
            'fecha_entrega' => $this->fecha_entrega?->format('Y-m-d'),
            'fecha_entrega_formateada' => $this->fecha_entrega?->format('d/m/Y'),
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'total' => number_format((float) $this->total, 2, '.', ''),
            'empresa' => $this->whenLoaded('empresa', function () {
                return new EmpresaResource($this->empresa);
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'detalles' => DetallePedidoResource::collection($this->whenLoaded('detalles')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
