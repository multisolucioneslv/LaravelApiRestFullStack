<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Obtener todas las conversaciones del usuario actual
     */
    public function getConversations()
    {
        $user = auth()->user();

        $conversations = Conversation::where(function ($query) use ($user) {
            $query->where('user1_id', $user->id)
                  ->orWhere('user2_id', $user->id);
        })
        ->with(['user1', 'user2', 'lastMessage.sender'])
        ->orderBy('last_message_at', 'desc')
        ->get()
        ->map(function ($conversation) use ($user) {
            $otherUser = $conversation->getOtherUser($user->id);
            return [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->avatar,
                ],
                'last_message' => $conversation->lastMessage ? [
                    'message' => $conversation->lastMessage->message,
                    'created_at' => $conversation->lastMessage->created_at,
                    'is_mine' => $conversation->lastMessage->sender_id === $user->id,
                ] : null,
                'unread_count' => $conversation->unreadMessagesCount($user->id),
                'last_message_at' => $conversation->last_message_at,
            ];
        });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Obtener o crear conversación con un usuario
     */
    public function getOrCreateConversation($userId)
    {
        $currentUser = auth()->user();

        // Verificar que el usuario existe y pertenece a la misma empresa
        $otherUser = User::where('id', $userId)
            ->where('empresa_id', $currentUser->empresa_id)
            ->firstOrFail();

        // Buscar conversación existente (en cualquier orden)
        $conversation = Conversation::where(function ($query) use ($currentUser, $otherUser) {
            $query->where(function ($q) use ($currentUser, $otherUser) {
                $q->where('user1_id', $currentUser->id)
                  ->where('user2_id', $otherUser->id);
            })->orWhere(function ($q) use ($currentUser, $otherUser) {
                $q->where('user1_id', $otherUser->id)
                  ->where('user2_id', $currentUser->id);
            });
        })->first();

        // Si no existe, crear nueva conversación
        if (!$conversation) {
            $conversation = Conversation::create([
                'user1_id' => $currentUser->id,
                'user2_id' => $otherUser->id,
                'empresa_id' => $currentUser->empresa_id,
            ]);
        }

        // Cargar mensajes
        $messages = $conversation->messages()
            ->with('sender')
            ->get()
            ->map(function ($message) use ($currentUser) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_mine' => $message->sender_id === $currentUser->id,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'avatar' => $message->sender->avatar,
                    ],
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->avatar,
                ],
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Enviar mensaje
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ], [
            'message.required' => 'El mensaje es obligatorio',
            'message.max' => 'El mensaje no puede exceder 5000 caracteres',
        ]);

        $currentUser = auth()->user();

        // Verificar que la conversación existe y el usuario es parte de ella
        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($currentUser) {
                $query->where('user1_id', $currentUser->id)
                      ->orWhere('user2_id', $currentUser->id);
            })
            ->firstOrFail();

        // Crear mensaje
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $currentUser->id,
            'message' => $request->message,
        ]);

        // Actualizar last_message_at de la conversación
        $conversation->update(['last_message_at' => now()]);

        // Cargar relación sender
        $message->load('sender');

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado correctamente',
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'is_mine' => true,
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'avatar' => $message->sender->avatar,
                ],
                'is_read' => $message->is_read,
                'created_at' => $message->created_at,
            ],
        ], 201);
    }

    /**
     * Marcar mensajes como leídos
     */
    public function markAsRead($conversationId)
    {
        $currentUser = auth()->user();

        // Verificar que la conversación existe y el usuario es parte de ella
        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($currentUser) {
                $query->where('user1_id', $currentUser->id)
                      ->orWhere('user2_id', $currentUser->id);
            })
            ->firstOrFail();

        // Marcar como leídos todos los mensajes que NO fueron enviados por el usuario actual
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Mensajes marcados como leídos',
        ]);
    }

    /**
     * Obtener contador de mensajes no leídos total
     */
    public function getUnreadCount()
    {
        $user = auth()->user();

        $unreadCount = Message::whereHas('conversation', function ($query) use ($user) {
            $query->where('user1_id', $user->id)
                  ->orWhere('user2_id', $user->id);
        })
        ->where('sender_id', '!=', $user->id)
        ->where('is_read', false)
        ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }
}
