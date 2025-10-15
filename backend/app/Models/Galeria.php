<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Galeria extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'galerias';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'imagenes',
        'galeriable_id',
        'galeriable_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'imagenes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELACIONES POLIMÓRFICAS
    // ==========================================

    /**
     * Relación polimórfica inversa
     * Una galería pertenece a cualquier modelo (Producto, Marca, etc.)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function galeriable()
    {
        return $this->morphTo();
    }

    // ==========================================
    // MÉTODOS AUXILIARES
    // ==========================================

    /**
     * Obtener la imagen principal de la galería
     *
     * @return string|null
     */
    public function getImagenPrincipalAttribute()
    {
        if (!$this->imagenes || count($this->imagenes) === 0) {
            return null;
        }

        // Buscar la imagen marcada como principal
        foreach ($this->imagenes as $imagen) {
            if (isset($imagen['es_principal']) && $imagen['es_principal']) {
                return $imagen['url'];
            }
        }

        // Si no hay ninguna marcada, retornar la primera (orden 0)
        $primeraImagen = collect($this->imagenes)->sortBy('orden')->first();
        return $primeraImagen['url'] ?? null;
    }
}
