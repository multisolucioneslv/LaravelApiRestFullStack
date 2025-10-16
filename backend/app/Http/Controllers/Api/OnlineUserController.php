<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnlineUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineUserController extends Controller
{
    /**
     * Obtener usuarios en línea (de la misma empresa)
     */
    public function getOnlineUsers(): JsonResponse
    {
        $currentUser = auth()->user();

        $onlineUsers = OnlineUser::with('user')
            ->where('empresa_id', $currentUser->empresa_id) // FILTRO POR EMPRESA
            ->active() // Scope que filtra últimos 5 minutos
            ->where('user_id', '!=', $currentUser->id) // Excluir al usuario actual
            ->get()
            ->map(function ($onlineUser) {
                return [
                    'id' => $onlineUser->user->id,
                    'name' => $onlineUser->user->name,
                    'avatar' => $onlineUser->user->avatar,
                    'status' => $onlineUser->status,
                    'last_activity' => $onlineUser->last_activity,
                ];
            });

        return response()->json([
            'success' => true,
            'online_users' => $onlineUsers,
            'count' => $onlineUsers->count(),
        ]);
    }

    /**
     * Marcar usuario como en línea
     */
    public function markOnline(): JsonResponse
    {
        $user = auth()->user();

        $onlineUser = OnlineUser::updateOrCreate(
            ['user_id' => $user->id],
            [
                'empresa_id' => $user->empresa_id,
                'last_activity' => now(),
                'status' => 'online',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Usuario marcado como en línea',
        ]);
    }

    /**
     * Actualizar última actividad (heartbeat)
     */
    public function updateActivity(): JsonResponse
    {
        $user = auth()->user();

        $onlineUser = OnlineUser::where('user_id', $user->id)->first();

        if ($onlineUser) {
            $onlineUser->updateActivity();
        } else {
            // Si no existe, crear
            OnlineUser::create([
                'user_id' => $user->id,
                'empresa_id' => $user->empresa_id,
                'last_activity' => now(),
                'status' => 'online',
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Marcar usuario como desconectado
     */
    public function markOffline(): JsonResponse
    {
        $user = auth()->user();

        OnlineUser::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario marcado como desconectado',
        ]);
    }
}
