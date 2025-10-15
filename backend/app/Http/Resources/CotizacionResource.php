<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CotizacionResource extends JsonResource
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
            'fecha_vencimiento' => $this->fecha_vencimiento?->format('Y-m-d'),
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'empresa_id' => $this->empresa_id,
            'user_id' => $this->user_id,
            'moneda_id' => $this->moneda_id,
            'tax_id' => $this->tax_id,
            'subtotal' => (float) $this->subtotal,
            'impuesto' => (float) $this->impuesto,
            'descuento' => (float) $this->descuento,
            'total' => (float) $this->total,

            // Relaciones
            'empresa' => $this->whenLoaded('empresa', function () {
                return [
                    'id' => $this->empresa->id,
                    'nombre' => $this->empresa->nombre,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'moneda' => $this->whenLoaded('moneda', function () {
                return $this->moneda ? [
                    'id' => $this->moneda->id,
                    'nombre' => $this->moneda->nombre,
                    'codigo' => $this->moneda->codigo,
                    'simbolo' => $this->moneda->simbolo,
                ] : null;
            }),
            'tax' => $this->whenLoaded('tax', function () {
                return $this->tax ? [
                    'id' => $this->tax->id,
                    'nombre' => $this->tax->nombre,
                    'porcentaje' => (float) $this->tax->porcentaje,
                ] : null;
            }),

            // Detalles de la cotización
            'detalles' => $this->whenLoaded('detalles', function () {
                return DetalleCotizacionResource::collection($this->detalles);
            }),

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
        ];
    }
}
