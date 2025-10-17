<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relación con la conversación de IA
     */
    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    /**
     * Agregar metadata al mensaje
     */
    public function addMetadata($key, $value)
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->update(['metadata' => $metadata]);
    }

    /**
     * Obtener metadata específica
     */
    public function getMetadata($key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Verificar si es un mensaje del usuario
     */
    public function isUserMessage()
    {
        return $this->role === 'user';
    }

    /**
     * Verificar si es un mensaje del asistente
     */
    public function isAssistantMessage()
    {
        return $this->role === 'assistant';
    }

    /**
     * Verificar si es un mensaje del sistema
     */
    public function isSystemMessage()
    {
        return $this->role === 'system';
    }
}
