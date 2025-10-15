<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatid\StoreChatidRequest;
use App\Http\Requests\Chatid\UpdateChatidRequest;
use App\Http\Resources\ChatidResource;
use App\Models\Chatid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatidController extends Controller
{
    /**
     * Listar chat IDs con paginación y búsqueda
     * Búsqueda por: idtelegram
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $chatids = Chatid::query()
            ->when($search, function ($query, $search) {
                $query->where('idtelegram', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ChatidResource::collection($chatids),
            'meta' => [
                'current_page' => $chatids->currentPage(),
                'last_page' => $chatids->lastPage(),
                'per_page' => $chatids->perPage(),
                'total' => $chatids->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo chat ID
     */
    public function store(StoreChatidRequest $request): JsonResponse
    {
        $chatid = Chatid::create([
            'idtelegram' => $request->idtelegram,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chat ID creado exitosamente',
            'data' => new ChatidResource($chatid),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un chat ID específico
     */
    public function show(int $id): JsonResponse
    {
        $chatid = Chatid::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new ChatidResource($chatid),
        ]);
    }

    /**
     * Actualizar chat ID
     */
    public function update(UpdateChatidRequest $request, int $id): JsonResponse
    {
        $chatid = Chatid::findOrFail($id);

        $chatid->update([
            'idtelegram' => $request->idtelegram,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chat ID actualizado exitosamente',
            'data' => new ChatidResource($chatid),
        ]);
    }

    /**
     * Eliminar un chat ID
     */
    public function destroy(int $id): JsonResponse
    {
        $chatid = Chatid::findOrFail($id);
        $chatid->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat ID eliminado exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples chat IDs (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:chatids,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Chatid::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} chat ID(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
