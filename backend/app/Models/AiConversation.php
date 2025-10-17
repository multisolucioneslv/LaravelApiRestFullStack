<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantScope;

class AiConversation extends Model
{
    use HasFactory, SoftDeletes, MultiTenantScope;

    protected $fillable = [
        'user_id',
        'empresa_id',
        'title',
        'context',
        'last_message_at',
    ];

    protected $casts = [
        'context' => 'array',
        'last_message_at' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relación con los mensajes
     */
    public function messages()
    {
        return $this->hasMany(AiMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Obtener el último mensaje
     */
    public function lastMessage()
    {
        return $this->hasOne(AiMessage::class)->latestOfMany();
    }

    /**
     * Generar título automáticamente basado en el primer mensaje
     */
    public function generateTitle()
    {
        $firstUserMessage = $this->messages()
            ->where('role', 'user')
            ->first();

        if ($firstUserMessage) {
            // Tomar las primeras 50 palabras del primer mensaje como título
            $title = \Illuminate\Support\Str::limit($firstUserMessage->content, 50, '...');
            $this->update(['title' => $title]);
        }
    }

    /**
     * Agregar contexto a la conversación
     */
    public function addContext($key, $value)
    {
        $context = $this->context ?? [];
        $context[$key] = $value;
        $this->update(['context' => $context]);
    }

    /**
     * Obtener contexto específico
     */
    public function getContext($key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }
}
