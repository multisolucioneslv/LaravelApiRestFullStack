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
        'currency_id',
        'email',
        'direccion',
        'logo',
        'favicon',
        'fondo_login',
        'zona_horaria',
        'horarios',
        'activo',
        'show_loading_effect',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
        'show_loading_effect' => 'boolean',
        'horarios' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES INVERSAS (Many-to-One)
    // ==========================================

    /**
     * Teléfono principal de la empresa (relación directa)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phone()
    {
        return $this->belongsTo(Phone::class, 'telefono_id');
    }

    /**
     * Teléfonos adicionales de la empresa (relación polimórfica)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function additionalPhones()
    {
        return $this->morphMany(Phone::class, 'phonable');
    }

    /**
     * Una empresa pertenece a una moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
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
