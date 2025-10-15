<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'usuario' => $this->usuario,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'activo' => (bool) $this->activo,
            'sexo_id' => $this->sexo_id,
            'telefono' => $this->telefono,
            'chatid' => $this->chatid,
            'cuenta' => $this->cuenta,
            'empresa_id' => $this->empresa_id,

            // Relaciones
            'sex' => $this->whenLoaded('sex', function () {
                return [
                    'id' => $this->sex->id,
                    'name' => $this->sex->name,
                ];
            }),
            'empresa' => $this->whenLoaded('empresa', function () {
                return [
                    'id' => $this->empresa->id,
                    'nombre' => $this->empresa->nombre,
                ];
            }),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,
                    ];
                });
            }),

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
