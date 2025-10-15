<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GaleriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Procesar el array de imágenes para convertir URLs relativas a absolutas
        $imagenesConUrls = [];
        if ($this->imagenes && is_array($this->imagenes)) {
            foreach ($this->imagenes as $imagen) {
                $imagenesConUrls[] = [
                    'url' => isset($imagen['url']) ? asset('storage/' . $imagen['url']) : null,
                    'orden' => $imagen['orden'] ?? 0,
                    'es_principal' => $imagen['es_principal'] ?? false,
                ];
            }
        }

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'imagenes' => $imagenesConUrls,
            'imagen_principal' => $this->imagen_principal ? asset('storage/' . $this->imagen_principal) : null,
            'cantidad_imagenes' => count($imagenesConUrls),
            'galeriable_type' => $this->galeriable_type,
            'galeriable_id' => $this->galeriable_id,

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
