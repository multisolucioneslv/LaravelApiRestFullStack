<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ventas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codigo',
        'fecha',
        'estado',
        'tipo_pago',
        'observaciones',
        'empresa_id',
        'user_id',
        'cotizacion_id',
        'moneda_id',
        'tax_id',
        'subtotal',
        'impuesto',
        'descuento',
        'total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'date',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES (Many-to-One)
    // ==========================================

    /**
     * Una venta pertenece a una empresa
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Una venta pertenece a un usuario (vendedor)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Una venta puede venir de una cotización
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Una venta tiene una moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    /**
     * Una venta tiene un impuesto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    // ==========================================
    // RELACIONES (One-to-Many)
    // ==========================================

    /**
     * Una venta tiene muchos detalles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}
