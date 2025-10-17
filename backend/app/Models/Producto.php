<?php

namespace App\Models;

use App\Traits\MultiTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes, MultiTenantScope;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'productos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'sku',
        'codigo_barras',
        'precio_compra',
        'precio_venta',
        'precio_mayoreo',
        'stock_minimo',
        'stock_actual',
        'unidad_medida',
        'imagen',
        'activo',
        'empresa_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_mayoreo' => 'decimal:2',
        'stock_minimo' => 'integer',
        'stock_actual' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES (Many-to-One)
    // ==========================================

    /**
     * Un producto pertenece a una empresa
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
     * Un producto puede tener muchos registros de inventario
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'producto_id');
    }

    // ==========================================
    // RELACIONES (Many-to-Many)
    // ==========================================

    /**
     * Un producto puede pertenecer a muchas categorías
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_producto', 'producto_id', 'categoria_id')
            ->withTimestamps();
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
        return $this->stock_actual < $this->stock_minimo;
    }

    /**
     * Verifica si el producto está disponible para venta
     *
     * @return bool
     */
    public function estaDisponible()
    {
        return $this->activo && $this->stock_actual > 0;
    }

    /**
     * Calcula el margen de ganancia (porcentaje)
     *
     * @return float
     */
    public function calcularMargenGanancia()
    {
        if ($this->precio_compra <= 0) {
            return 0;
        }

        return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
    }

    /**
     * Actualizar stock del producto
     *
     * @param int $cantidad
     * @param string $tipo (incrementar|decrementar)
     * @return bool
     */
    public function actualizarStock(int $cantidad, string $tipo = 'incrementar')
    {
        if ($tipo === 'incrementar') {
            $this->stock_actual += $cantidad;
        } elseif ($tipo === 'decrementar') {
            $this->stock_actual -= $cantidad;
            if ($this->stock_actual < 0) {
                $this->stock_actual = 0;
            }
        }

        return $this->save();
    }
}
