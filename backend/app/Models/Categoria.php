<?php

namespace App\Models;

use App\Traits\MultiTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Categoria extends Model
{
    use HasFactory, SoftDeletes, MultiTenantScope;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categorias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'slug',
        'icono',
        'color',
        'activo',
        'empresa_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // EVENTOS DEL MODELO
    // ==========================================

    protected static function boot()
    {
        parent::boot();

        // Generar slug automáticamente antes de crear
        static::creating(function ($categoria) {
            if (empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nombre);
            }
        });

        // Actualizar slug al actualizar nombre
        static::updating(function ($categoria) {
            if ($categoria->isDirty('nombre') && empty($categoria->slug)) {
                $categoria->slug = Str::slug($categoria->nombre);
            }
        });
    }

    // ==========================================
    // RELACIONES (Many-to-One)
    // ==========================================

    /**
     * Una categoría pertenece a una empresa
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // ==========================================
    // RELACIONES (Many-to-Many)
    // ==========================================

    /**
     * Una categoría puede tener muchos productos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'categoria_producto', 'categoria_id', 'producto_id')
            ->withTimestamps();
    }

    // ==========================================
    // MÉTODOS DE UTILIDAD
    // ==========================================

    /**
     * Cuenta cuántos productos tiene la categoría
     *
     * @return int
     */
    public function contarProductos()
    {
        return $this->productos()->count();
    }

    /**
     * Obtiene productos activos de la categoría
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productosActivos()
    {
        return $this->productos()->where('activo', true);
    }
}
