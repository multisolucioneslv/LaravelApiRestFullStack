<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RutaResource extends JsonResource
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
            'sistema_id' => $this->sistema_id,
            'sistema' => $this->when($this->relationLoaded('sistema'), function () {
                return [
                    'id' => $this->sistema->id,
                    'nombre' => $this->sistema->nombre,
                    'codigo' => $this->sistema->codigo,
                ];
            }),
            'ruta' => $this->ruta,
            'metodo' => $this->metodo,
            'descripcion' => $this->descripcion,
            'controlador' => $this->controlador,
            'accion' => $this->accion,
            'middleware' => $this->middleware,
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
