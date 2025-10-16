<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantScope;

class Conversation extends Model
{
    use HasFactory, SoftDeletes, MultiTenantScope;

    protected $fillable = [
        'user1_id',
        'user2_id',
        'empresa_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Relación con el primer usuario
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * Relación con el segundo usuario
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
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
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Obtener el último mensaje
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Obtener el otro usuario de la conversación (no el actual)
     */
    public function getOtherUser($currentUserId)
    {
        return $this->user1_id === $currentUserId ? $this->user2 : $this->user1;
    }

    /**
     * Contar mensajes no leídos para un usuario
     */
    public function unreadMessagesCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }
}
