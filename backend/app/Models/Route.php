<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * NOTA IMPORTANTE:
     * Este modelo NO es para rutas de entrega de pedidos.
     * Es para guardar las RUTAS API del sistema (endpoints registrados).
     */

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'routes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sistema_id',
        'ruta',
        'metodo',
        'descripcion',
        'controlador',
        'accion',
        'middleware',
        'activo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'middleware' => 'array',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES (Many-to-One)
    // ==========================================

    /**
     * Una ruta API pertenece a un sistema
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sistema()
    {
        return $this->belongsTo(Sistema::class, 'sistema_id');
    }
}
