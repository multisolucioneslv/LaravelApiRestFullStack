<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'empresas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'telefono_id',
        'moneda_id',
        'email',
        'direccion',
        'logo',
        'favicon',
        'fondo_login',
        'zona_horaria',
        'horarios',
        'activo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
        'horarios' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES INVERSAS (Many-to-One)
    // ==========================================

    /**
     * Una empresa pertenece a un teléfono
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function telefono()
    {
        return $this->belongsTo(Telefono::class, 'telefono_id');
    }

    /**
     * Una empresa pertenece a una moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    // ==========================================
    // RELACIONES DIRECTAS (One-to-Many)
    // ==========================================

    /**
     * Una empresa tiene muchos usuarios
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    /**
     * Una empresa tiene muchas bodegas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bodegas()
    {
        return $this->hasMany(Bodega::class, 'empresa_id');
    }

    /**
     * Una empresa tiene muchos inventarios
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'empresa_id');
    }

    /**
     * Una empresa tiene muchas cotizaciones
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'empresa_id');
    }

    /**
     * Una empresa tiene muchas ventas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'empresa_id');
    }

    /**
     * Una empresa tiene muchos pedidos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'empresa_id');
    }

    /**
     * Una empresa tiene muchos impuestos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxes()
    {
        return $this->hasMany(Tax::class, 'empresa_id');
    }
}
