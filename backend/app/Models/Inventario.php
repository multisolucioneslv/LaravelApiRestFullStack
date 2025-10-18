<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'producto_id',
        'galeria_id',
        'bodega_id',
        'empresa_id',
        'cantidad',
        'minimo',
        'maximo',
        'precio_compra',
        'precio_venta',
        'activo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad' => 'integer',
        'minimo' => 'integer',
        'maximo' => 'integer',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES (Many-to-One)
    // ==========================================

    /**
     * Un inventario pertenece a un producto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Un inventario pertenece a una galería
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function galeria()
    {
        return $this->belongsTo(Galeria::class, 'galeria_id');
    }

    /**
     * Un inventario pertenece a una bodega
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    /**
     * Un inventario pertenece a una empresa
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // ==========================================
    // RELACIONES (One-to-Many)
    // ==========================================

    /**
     * Un inventario puede estar en muchas cotizaciones
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalleCotizaciones()
    {
        return $this->hasMany(DetalleCotizacion::class, 'inventario_id');
    }

    /**
     * Un inventario puede estar en muchas ventas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'inventario_id');
    }

    // ==========================================
    // MÉTODOS DE UTILIDAD
    // ==========================================

    /**
     * Verifica si el stock está por debajo del mínimo
     *
     * @return bool
     */
    public function esBajoStock()
    {
        return $this->cantidad < $this->minimo;
    }

    /**
     * Verifica si el stock excede el máximo
     *
     * @return bool
     */
    public function excedeMaximo()
    {
        return $this->cantidad > $this->maximo;
    }
}
