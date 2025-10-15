<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sistema extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sistemas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'version',
        'configuracion',
        'logo',
        'activo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES (One-to-Many)
    // ==========================================

    /**
     * Un sistema tiene muchas rutas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rutas()
    {
        return $this->hasMany(Ruta::class, 'sistema_id');
    }
}
