<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\OpenAIService;
use App\Services\N8nWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    protected $openAIService;
    protected $n8nService;

    public function __construct(OpenAIService $openAIService, N8nWebhookService $n8nService)
    {
        $this->openAIService = $openAIService;
        $this->n8nService = $n8nService;
    }

    /**
     * Obtener todas las conversaciones del usuario actual
     */
    public function getConversations()
    {
        $user = auth()->user();

        $conversations = AiConversation::where('user_id', $user->id)
            ->with(['lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title ?? 'Nueva conversación',
                    'last_message' => $conversation->lastMessage ? [
                        'content' => \Illuminate\Support\Str::limit($conversation->lastMessage->content, 100),
                        'role' => $conversation->lastMessage->role,
                        'created_at' => $conversation->lastMessage->created_at,
                    ] : null,
                    'last_message_at' => $conversation->last_message_at,
                    'created_at' => $conversation->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Crear una nueva conversación
     */
    public function createConversation(Request $request)
    {
        $user = auth()->user();

        $conversation = AiConversation::create([
            'user_id' => $user->id,
            'empresa_id' => $user->empresa_id,
            'title' => $request->input('title', null),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversación creada correctamente',
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title ?? 'Nueva conversación',
                'created_at' => $conversation->created_at,
            ],
        ], 201);
    }

    /**
     * Obtener una conversación específica con sus mensajes
     */
    public function getConversation($conversationId)
    {
        $user = auth()->user();

        $conversation = AiConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->firstOrFail();

        $messages = $conversation->messages->map(function ($message) {
            return [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'metadata' => $message->metadata,
                'created_at' => $message->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title ?? 'Nueva conversación',
                'messages' => $messages,
                'created_at' => $conversation->created_at,
            ],
        ]);
    }

    /**
     * Enviar mensaje y obtener respuesta de IA
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ], [
            'message.required' => 'El mensaje es obligatorio',
            'message.max' => 'El mensaje no puede exceder 5000 caracteres',
        ]);

        $user = auth()->user();

        // Verificar que la conversación existe y pertenece al usuario
        $conversation = AiConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // 1. Guardar mensaje del usuario
            $userMessage = AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $request->message,
            ]);

            // 2. Actualizar last_message_at
            $conversation->update(['last_message_at' => now()]);

            // 3. Generar título si es el primer mensaje
            if (!$conversation->title) {
                $conversation->generateTitle();
            }

            // 4. Detectar intención y obtener datos de la base de datos
            $databaseData = $this->detectAndQueryDatabase($request->message, $user);

            // 5. Preparar contexto para OpenAI
            $messages = $this->prepareOpenAIMessages($conversation, $request->message, $databaseData);

            // 6. Obtener respuesta de OpenAI
            $aiResponse = $this->openAIService->chat($messages);

            if (!$aiResponse['success']) {
                throw new \Exception('Error al obtener respuesta de la IA: ' . ($aiResponse['error'] ?? 'Desconocido'));
            }

            // 7. Guardar respuesta del asistente
            $assistantMessage = AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $aiResponse['content'],
                'metadata' => [
                    'model' => $aiResponse['model'] ?? null,
                    'tokens_used' => $aiResponse['usage'] ?? null,
                    'database_data_used' => $databaseData !== null,
                ],
            ]);

            // 8. Actualizar contexto de la conversación si hay datos de la BD
            if ($databaseData) {
                $conversation->addContext('last_database_query', [
                    'type' => $databaseData['type'],
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado correctamente',
                'data' => [
                    'user_message' => [
                        'id' => $userMessage->id,
                        'role' => 'user',
                        'content' => $userMessage->content,
                        'created_at' => $userMessage->created_at,
                    ],
                    'assistant_message' => [
                        'id' => $assistantMessage->id,
                        'role' => 'assistant',
                        'content' => $assistantMessage->content,
                        'created_at' => $assistantMessage->created_at,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en AIChatController@sendMessage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el mensaje',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar una conversación
     */
    public function deleteConversation($conversationId)
    {
        $user = auth()->user();

        $conversation = AiConversation::where('id', $conversationId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversación eliminada correctamente',
        ]);
    }

    /**
     * Detectar intención del usuario y obtener datos de la base de datos
     */
    protected function detectAndQueryDatabase($message, $user)
    {
        $messageLower = strtolower($message);

        // Detectar consultas sobre usuarios
        if (preg_match('/(usuario|users?|listar usuarios|mostrar usuarios|cuántos usuarios|registrados)/i', $message)) {
            $usuarios = DB::table('users')
                ->where('empresa_id', $user->empresa_id)
                ->whereNull('deleted_at')
                ->select('id', 'name', 'email', 'empresa_id', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return [
                'type' => 'usuarios',
                'data' => $usuarios,
            ];
        }

        // Detectar consultas sobre empresas
        if (preg_match('/(empresa|companies|listar empresas|mostrar empresas)/i', $message)) {
            $empresas = DB::table('empresas')
                ->whereNull('deleted_at')
                ->select('id', 'nombre', 'rfc', 'telefono', 'email', 'created_at')
                ->orderBy('nombre', 'asc')
                ->get();

            return [
                'type' => 'empresas',
                'data' => $empresas,
            ];
        }

        // Detectar consultas sobre productos
        if (preg_match('/(producto|products?|listar productos|mostrar productos|inventario)/i', $message)) {
            $productos = DB::table('productos as p')
                ->leftJoin('inventarios as i', 'p.id', '=', 'i.producto_id')
                ->where('p.empresa_id', $user->empresa_id)
                ->whereNull('p.deleted_at')
                ->select(
                    'p.id',
                    'p.nombre',
                    'p.descripcion',
                    DB::raw('p.precio_venta as precio'),
                    DB::raw('p.stock_actual as stock'),
                    'p.empresa_id',
                    DB::raw('COUNT(i.id) as total_inventario')
                )
                ->groupBy('p.id', 'p.nombre', 'p.descripcion', 'p.precio_venta', 'p.stock_actual', 'p.empresa_id')
                ->orderBy('p.nombre', 'asc')
                ->limit(50)
                ->get();

            return [
                'type' => 'productos',
                'data' => $productos,
            ];
        }

        // Detectar consultas sobre proveedores
        if (preg_match('/(proveedor|suppliers?|listar proveedores)/i', $message)) {
            $proveedores = DB::table('proveedores')
                ->where('empresa_id', $user->empresa_id)
                ->whereNull('deleted_at')
                ->select('id', 'nombre', 'telefono', 'email', 'empresa_id', 'created_at')
                ->orderBy('nombre', 'asc')
                ->limit(50)
                ->get();

            return [
                'type' => 'proveedores',
                'data' => $proveedores,
            ];
        }

        // Detectar consultas sobre categorías
        if (preg_match('/(categoría|categor[ií]a|categories|listar categorías)/i', $message)) {
            $categorias = DB::table('categorias as c')
                ->leftJoin('categoria_producto as cp', 'c.id', '=', 'cp.categoria_id')
                ->where('c.empresa_id', $user->empresa_id)
                ->whereNull('c.deleted_at')
                ->select(
                    'c.id',
                    'c.nombre',
                    'c.descripcion',
                    DB::raw('COUNT(cp.producto_id) as total_productos')
                )
                ->groupBy('c.id', 'c.nombre', 'c.descripcion')
                ->orderBy('c.nombre', 'asc')
                ->get();

            return [
                'type' => 'categorias',
                'data' => $categorias,
            ];
        }

        // Detectar consultas sobre ventas y productos más vendidos
        if (preg_match('/(venta|ventas|vendido|más vendido|top.*vendido|productos.*vendido|mejor.*venta)/i', $message)) {
            // Extraer número si se especifica (ej: "top 5", "10 productos")
            $limit = 10; // Por defecto 10
            if (preg_match('/(\d+)/', $message, $matches)) {
                $limit = min((int)$matches[1], 50); // Máximo 50
            }

            $productosVendidos = DB::table('detalle_ventas as dv')
                ->join('inventarios as i', 'dv.inventario_id', '=', 'i.id')
                ->join('productos as p', 'i.producto_id', '=', 'p.id')
                ->join('ventas as v', 'dv.venta_id', '=', 'v.id')
                ->where('p.empresa_id', $user->empresa_id)
                ->whereNull('p.deleted_at')
                ->whereNull('v.deleted_at')
                ->where('v.estado', 'completada')
                ->select(
                    'p.id',
                    'p.nombre',
                    'p.descripcion',
                    DB::raw('SUM(dv.cantidad) as total_vendido'),
                    DB::raw('SUM(dv.subtotal) as ingresos_totales'),
                    DB::raw('AVG(dv.precio_unitario) as precio_promedio')
                )
                ->groupBy('p.id', 'p.nombre', 'p.descripcion')
                ->orderBy('total_vendido', 'desc')
                ->limit($limit)
                ->get();

            return [
                'type' => 'ventas',
                'data' => $productosVendidos,
            ];
        }

        return null;
    }

    /**
     * Preparar mensajes para enviar a OpenAI
     */
    protected function prepareOpenAIMessages($conversation, $userMessage, $databaseData = null)
    {
        $messages = [];

        // Mensaje de sistema con instrucciones
        $systemInstructions = "Eres un asistente virtual inteligente para un sistema ERP empresarial. ";
        $systemInstructions .= "Tu objetivo es ayudar a los usuarios con consultas sobre su empresa, productos, usuarios, proveedores, categorías y otras funcionalidades del sistema. ";
        $systemInstructions .= "Responde de manera clara, concisa y profesional. ";
        $systemInstructions .= "Cuando se te proporcionen datos de la base de datos, úsalos para dar respuestas precisas y útiles. ";
        $systemInstructions .= "Si no tienes información suficiente, solicita al usuario que especifique mejor su consulta. ";
        $systemInstructions .= "Siempre responde en español latinoamericano.";

        $messages[] = $this->openAIService->createSystemMessage($systemInstructions);

        // Historial de mensajes previos (últimos 10 para no saturar el contexto)
        $previousMessages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse();

        foreach ($previousMessages as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        // Mensaje actual del usuario
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // Si hay datos de la base de datos, agregar contexto adicional
        if ($databaseData) {
            $contextMessage = "Información disponible de la base de datos:\n\n";
            $contextMessage .= json_encode($databaseData['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            $messages[] = [
                'role' => 'system',
                'content' => $contextMessage,
            ];
        }

        return $messages;
    }
}
