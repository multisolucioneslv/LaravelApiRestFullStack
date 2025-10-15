<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Moneda extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'monedas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo',
        'nombre',
        'simbolo',
        'tasa_cambio',
        'activo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tasa_cambio' => 'decimal:4',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES (One-to-Many)
    // ==========================================

    /**
     * Una moneda puede ser usada en muchas empresas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'moneda_id');
    }

    /**
     * Una moneda puede ser usada en muchas cotizaciones
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'moneda_id');
    }

    /**
     * Una moneda puede ser usada en muchas ventas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'moneda_id');
    }
}
