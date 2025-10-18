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

            // 4. Obtener empresa y configurar credenciales personalizadas si existen
            $empresa = $user->empresa;

            Log::info('Configuración AI de empresa', [
                'empresa_id' => $empresa->id,
                'ai_detection_mode' => $empresa->ai_detection_mode,
                'has_custom_api_key' => !empty($empresa->openai_api_key),
            ]);

            if ($empresa->openai_api_key) {
                $this->openAIService->setCustomCredentials(
                    $empresa->openai_api_key,
                    $empresa->openai_model,
                    $empresa->openai_max_tokens,
                    $empresa->openai_temperature
                );

                Log::info('Credenciales OpenAI personalizadas configuradas', [
                    'model' => $empresa->openai_model,
                    'max_tokens' => $empresa->openai_max_tokens,
                    'temperature' => $empresa->openai_temperature,
                ]);
            }

            // 5. Detectar intención según modo configurado
            $detectionMode = $empresa->ai_detection_mode ?? 'regex';
            $databaseData = null;

            switch ($detectionMode) {
                case 'function_calling':
                    Log::info('Usando modo: function_calling');
                    $databaseData = $this->detectWithFunctionCalling($request->message, $user);
                    break;

                case 'double_call':
                    Log::info('Usando modo: double_call');
                    $databaseData = $this->detectWithDoubleCall($request->message, $user);
                    break;

                case 'regex':
                default:
                    Log::info('Usando modo: regex (por defecto)');
                    $databaseData = $this->detectAndQueryDatabase($request->message, $user);
                    break;
            }

            // 6. Preparar contexto para OpenAI
            $messages = $this->prepareOpenAIMessages($conversation, $request->message, $databaseData);

            // 7. Obtener respuesta de OpenAI
            $aiResponse = $this->openAIService->chat($messages);

            if (!$aiResponse['success']) {
                throw new \Exception('Error al obtener respuesta de la IA: ' . ($aiResponse['error'] ?? 'Desconocido'));
            }

            // 8. Guardar respuesta del asistente
            $assistantMessage = AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $aiResponse['content'],
                'metadata' => [
                    'model' => $aiResponse['model'] ?? null,
                    'tokens_used' => $aiResponse['usage'] ?? null,
                    'database_data_used' => $databaseData !== null,
                    'detection_mode' => $detectionMode,
                ],
            ]);

            // 9. Actualizar contexto de la conversación si hay datos de la BD
            if ($databaseData) {
                $conversation->addContext('last_database_query', [
                    'type' => $databaseData['type'],
                    'timestamp' => now()->toDateTimeString(),
                    'detection_mode' => $detectionMode,
                ]);
            }

            // 10. Actualizar estadísticas de uso de AI en la empresa
            $empresa->increment('ai_total_queries');
            $empresa->update([
                'ai_last_used_at' => now(),
            ]);

            Log::info('Estadísticas AI actualizadas', [
                'empresa_id' => $empresa->id,
                'total_queries' => $empresa->ai_total_queries,
            ]);

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
     * Detectar intención del usuario y obtener datos de la base de datos (modo REGEX)
     */
    protected function detectAndQueryDatabase($message, $user)
    {
        // Detectar consultas sobre ventas y productos más vendidos (PRIORIDAD MÁXIMA)
        if (preg_match('/(venta|ventas|vendido|más vendido|top.*vendido|productos.*vendido|mejor.*venta)/i', $message)) {
            // Extraer número si se especifica (ej: "top 5", "10 productos")
            $limit = 10; // Por defecto 10
            if (preg_match('/(\d+)/', $message, $matches)) {
                $limit = min((int)$matches[1], 50); // Máximo 50
            }

            return [
                'type' => 'ventas',
                'data' => $this->queryVentas(['limit' => $limit], $user),
            ];
        }

        // Detectar consultas sobre productos (ANTES de usuarios para evitar conflictos)
        if (preg_match('/(producto|products?|inventario|listar productos|mostrar productos|cuántos productos)/i', $message)) {
            return [
                'type' => 'productos',
                'data' => $this->queryProductos($user),
            ];
        }

        // Detectar consultas sobre usuarios (MÁS ESPECÍFICO)
        if (preg_match('/(usuarios?\s+(registrados?|activos?)|cuántos usuarios|listar usuarios|mostrar usuarios)/i', $message)) {
            return [
                'type' => 'usuarios',
                'data' => $this->queryUsuarios($user),
            ];
        }

        // Detectar consultas sobre empresas
        if (preg_match('/(empresa|companies|listar empresas|mostrar empresas)/i', $message)) {
            return [
                'type' => 'empresas',
                'data' => $this->queryEmpresas($user),
            ];
        }

        // Detectar consultas sobre proveedores
        if (preg_match('/(proveedor|suppliers?|listar proveedores)/i', $message)) {
            return [
                'type' => 'proveedores',
                'data' => $this->queryProveedores($user),
            ];
        }

        // Detectar consultas sobre categorías
        if (preg_match('/(categoría|categor[ií]a|categories|listar categorías)/i', $message)) {
            return [
                'type' => 'categorias',
                'data' => $this->queryCategorias($user),
            ];
        }

        return null;
    }

    /**
     * Detectar intención usando Function Calling de OpenAI
     */
    protected function detectWithFunctionCalling($message, $user)
    {
        // Definir funciones disponibles para OpenAI
        $functions = [
            [
                'name' => 'query_ventas',
                'description' => 'Consultar información sobre ventas y productos más vendidos',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Número de resultados a retornar (máximo 50)',
                            'default' => 10
                        ]
                    ]
                ]
            ],
            [
                'name' => 'query_productos',
                'description' => 'Consultar información sobre productos e inventario',
                'parameters' => [
                    'type' => 'object',
                    'properties' => []
                ]
            ],
            [
                'name' => 'query_usuarios',
                'description' => 'Consultar información sobre usuarios registrados en el sistema',
                'parameters' => [
                    'type' => 'object',
                    'properties' => []
                ]
            ],
            [
                'name' => 'query_empresas',
                'description' => 'Consultar información sobre empresas registradas',
                'parameters' => [
                    'type' => 'object',
                    'properties' => []
                ]
            ],
            [
                'name' => 'query_proveedores',
                'description' => 'Consultar información sobre proveedores',
                'parameters' => [
                    'type' => 'object',
                    'properties' => []
                ]
            ],
            [
                'name' => 'query_categorias',
                'description' => 'Consultar información sobre categorías de productos',
                'parameters' => [
                    'type' => 'object',
                    'properties' => []
                ]
            ],
        ];

        try {
            // Llamar a OpenAI con function calling
            $response = $this->openAIService->chatWithFunctions([
                ['role' => 'user', 'content' => $message]
            ], $functions);

            Log::info('Respuesta de function calling', ['response' => $response]);

            // Si OpenAI decidió llamar a una función
            if (isset($response['function_call'])) {
                $functionName = $response['function_call']['name'];
                $arguments = json_decode($response['function_call']['arguments'], true) ?? [];

                Log::info('Function call detectada', [
                    'function' => $functionName,
                    'arguments' => $arguments
                ]);

                // Ejecutar la función correspondiente y retornar datos
                return $this->executeDatabaseFunction($functionName, $arguments, $user);
            }

            Log::info('No se detectó function call');
            return null;

        } catch (\Exception $e) {
            Log::error('Error en detectWithFunctionCalling', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Detectar intención usando doble llamada a OpenAI
     */
    protected function detectWithDoubleCall($message, $user)
    {
        try {
            // Primera llamada: Detectar intención
            $intentResponse = $this->openAIService->detectIntent($message);

            Log::info('Respuesta de detectIntent', ['response' => $intentResponse]);

            if (!$intentResponse['success']) {
                Log::warning('No se pudo detectar intención', ['response' => $intentResponse]);
                return null;
            }

            $intent = $intentResponse['intent'];

            Log::info('Intención detectada', ['intent' => $intent]);

            // Mapear intención a query de base de datos
            $intentMapping = [
                'ventas' => 'ventas',
                'productos' => 'productos',
                'usuarios' => 'usuarios',
                'empresas' => 'empresas',
                'proveedores' => 'proveedores',
                'categorias' => 'categorias',
            ];

            if (isset($intentMapping[$intent])) {
                return $this->queryDatabaseByIntent($intentMapping[$intent], $user);
            }

            Log::info('Intención no mapeada a consulta de BD', ['intent' => $intent]);
            return null;

        } catch (\Exception $e) {
            Log::error('Error en detectWithDoubleCall', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Ejecutar función de base de datos según function calling
     */
    protected function executeDatabaseFunction($functionName, $arguments, $user)
    {
        switch ($functionName) {
            case 'query_ventas':
                return [
                    'type' => 'ventas',
                    'data' => $this->queryVentas($arguments, $user)
                ];

            case 'query_productos':
                return [
                    'type' => 'productos',
                    'data' => $this->queryProductos($user)
                ];

            case 'query_usuarios':
                return [
                    'type' => 'usuarios',
                    'data' => $this->queryUsuarios($user)
                ];

            case 'query_empresas':
                return [
                    'type' => 'empresas',
                    'data' => $this->queryEmpresas($user)
                ];

            case 'query_proveedores':
                return [
                    'type' => 'proveedores',
                    'data' => $this->queryProveedores($user)
                ];

            case 'query_categorias':
                return [
                    'type' => 'categorias',
                    'data' => $this->queryCategorias($user)
                ];

            default:
                Log::warning('Función no reconocida', ['function' => $functionName]);
                return null;
        }
    }

    /**
     * Consultar base de datos según intención detectada
     */
    protected function queryDatabaseByIntent($intent, $user)
    {
        switch ($intent) {
            case 'ventas':
                return [
                    'type' => 'ventas',
                    'data' => $this->queryVentas([], $user)
                ];

            case 'productos':
                return [
                    'type' => 'productos',
                    'data' => $this->queryProductos($user)
                ];

            case 'usuarios':
                return [
                    'type' => 'usuarios',
                    'data' => $this->queryUsuarios($user)
                ];

            case 'empresas':
                return [
                    'type' => 'empresas',
                    'data' => $this->queryEmpresas($user)
                ];

            case 'proveedores':
                return [
                    'type' => 'proveedores',
                    'data' => $this->queryProveedores($user)
                ];

            case 'categorias':
                return [
                    'type' => 'categorias',
                    'data' => $this->queryCategorias($user)
                ];

            default:
                Log::warning('Intención no reconocida', ['intent' => $intent]);
                return null;
        }
    }

    /**
     * Query: Consultar ventas y productos más vendidos
     */
    protected function queryVentas($arguments, $user)
    {
        $limit = $arguments['limit'] ?? 10;
        $limit = min($limit, 50); // Máximo 50

        return DB::table('detalle_ventas as dv')
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
    }

    /**
     * Query: Consultar productos
     */
    protected function queryProductos($user)
    {
        return DB::table('productos as p')
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
    }

    /**
     * Query: Consultar usuarios
     */
    protected function queryUsuarios($user)
    {
        return DB::table('users')
            ->where('empresa_id', $user->empresa_id)
            ->whereNull('deleted_at')
            ->select('id', 'name', 'email', 'empresa_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Query: Consultar empresas
     */
    protected function queryEmpresas($user)
    {
        return DB::table('empresas')
            ->whereNull('deleted_at')
            ->select('id', 'nombre', 'rfc', 'telefono', 'email', 'created_at')
            ->orderBy('nombre', 'asc')
            ->get();
    }

    /**
     * Query: Consultar proveedores
     */
    protected function queryProveedores($user)
    {
        return DB::table('proveedores')
            ->where('empresa_id', $user->empresa_id)
            ->whereNull('deleted_at')
            ->select('id', 'nombre', 'telefono', 'email', 'empresa_id', 'created_at')
            ->orderBy('nombre', 'asc')
            ->limit(50)
            ->get();
    }

    /**
     * Query: Consultar categorías
     */
    protected function queryCategorias($user)
    {
        return DB::table('categorias as c')
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
