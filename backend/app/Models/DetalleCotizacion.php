<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCotizacion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'detalle_cotizaciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cotizacion_id',
        'inventario_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES (Many-to-One)
    // ==========================================

    /**
     * Un detalle pertenece a una cotización
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Un detalle pertenece a un inventario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }
}
