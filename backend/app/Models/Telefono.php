<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Telefono extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'telefonos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'telefono',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES (One-to-Many)
    // ==========================================

    /**
     * Un teléfono puede pertenecer a muchos usuarios
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'telefono_id');
    }

    /**
     * Un teléfono puede pertenecer a muchas empresas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'telefono_id');
    }
}
