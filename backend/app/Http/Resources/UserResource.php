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
            'phone_id' => $this->phone_id,
            'chatid_id' => $this->chatid_id,
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
            'phone' => $this->whenLoaded('phone', function () {
                return [
                    'id' => $this->phone->id,
                    'telefono' => $this->phone->telefono,
                ];
            }),
            'chatid' => $this->whenLoaded('chatid', function () {
                return [
                    'id' => $this->chatid->id,
                    'idtelegram' => $this->chatid->idtelegram,
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
