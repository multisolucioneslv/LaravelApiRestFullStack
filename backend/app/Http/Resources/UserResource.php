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
            'gender_id' => $this->gender_id,
            'cuenta' => $this->cuenta,
            'empresa_id' => $this->empresa_id,

            // Relaciones
            'gender' => $this->whenLoaded('gender', function () {
                return [
                    'id' => $this->gender->id,
                    'sexo' => $this->gender->sexo,
                    'inicial' => $this->gender->inicial,
                ];
            }),
            'phones' => $this->when(true, function () {
                $allPhones = collect();

                // Agregar teléfono principal si existe
                if ($this->relationLoaded('phone') && $this->phone) {
                    $allPhones->push([
                        'id' => $this->phone->id,
                        'telefono' => $this->phone->telefono,
                        'is_primary' => true,
                    ]);
                }

                // Agregar teléfonos adicionales
                if ($this->relationLoaded('additionalPhones')) {
                    foreach ($this->additionalPhones as $phone) {
                        $allPhones->push([
                            'id' => $phone->id,
                            'telefono' => $phone->telefono,
                            'is_primary' => false,
                        ]);
                    }
                }

                return $allPhones->values()->all();
            }),
            'chatid' => $this->when(true, function () {
                if ($this->relationLoaded('chatidPrimary') && $this->chatidPrimary) {
                    return [
                        'id' => $this->chatidPrimary->id,
                        'idtelegram' => $this->chatidPrimary->idtelegram,
                    ];
                }
                return null;
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
