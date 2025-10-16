<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantScope;

class OnlineUser extends Model
{
    use HasFactory, MultiTenantScope;

    protected $fillable = [
        'user_id',
        'empresa_id',
        'last_activity',
        'status',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Actualizar última actividad
     */
    public function updateActivity()
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Verificar si el usuario está activo (último actividad < 5 minutos)
     */
    public function isActive()
    {
        return $this->last_activity->diffInMinutes(now()) < 5;
    }

    /**
     * Scope para usuarios activos (menos de 5 minutos de inactividad)
     */
    public function scopeActive($query)
    {
        return $query->where('last_activity', '>=', now()->subMinutes(5));
    }
}
