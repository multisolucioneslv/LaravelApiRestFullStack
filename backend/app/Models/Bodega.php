<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bodega extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bodegas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'empresa_id',
        'activo',
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
    // RELACIONES (Many-to-One)
    // ==========================================

    /**
     * Una bodega pertenece a una empresa
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
     * Una bodega tiene muchos inventarios
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'bodega_id');
    }
}
