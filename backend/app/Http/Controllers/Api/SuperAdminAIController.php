<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SuperAdminAIController extends Controller
{
    /**
     * Constructor - Verificar que solo SuperAdmin acceda
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->hasRole('SuperAdmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo SuperAdmin puede acceder a esta funcionalidad.',
                ], 403);
            }
            return $next($request);
        });
    }

    /**
     * Listar todas las empresas con su configuración de AI
     */
    public function index(Request $request)
    {
        try {
            // Obtener parámetros de búsqueda y filtros
            $search = $request->input('search');
            $aiEnabled = $request->input('ai_enabled');
            $perPage = $request->input('per_page', 15);

            $query = Empresa::query();

            // Filtrar por búsqueda
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            // Filtrar por estado de AI
            if ($aiEnabled !== null) {
                $query->where('ai_chat_enabled', $aiEnabled);
            }

            $empresas = $query->paginate($perPage);

            $data = $empresas->map(function ($empresa) {
                return [
                    'id' => $empresa->id,
                    'nombre' => $empresa->nombre,
                    'email' => $empresa->email,
                    'activo' => $empresa->activo,
                    'ai_chat_enabled' => $empresa->ai_chat_enabled,
                    'ai_detection_mode' => $empresa->ai_detection_mode,
                    'ai_monthly_budget' => $empresa->ai_monthly_budget,
                    'ai_monthly_usage' => $empresa->ai_monthly_usage,
                    'ai_usage_reset_date' => $empresa->ai_usage_reset_date,
                    'ai_total_queries' => $empresa->ai_total_queries,
                    'ai_last_used_at' => $empresa->ai_last_used_at,
                    'has_own_api_key' => !empty($empresa->openai_api_key),
                    'users_count' => $empresa->users()->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => $empresas->total(),
                    'per_page' => $empresas->perPage(),
                    'current_page' => $empresas->currentPage(),
                    'last_page' => $empresas->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener empresas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Habilitar o deshabilitar AI Chat para una empresa específica
     */
    public function toggleAI(Request $request, $empresaId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ai_chat_enabled' => 'required|boolean',
            ], [
                'ai_chat_enabled.required' => 'Debe especificar si habilitar o deshabilitar AI',
                'ai_chat_enabled.boolean' => 'El valor debe ser verdadero o falso',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $empresa = Empresa::findOrFail($empresaId);

            $empresa->update([
                'ai_chat_enabled' => $request->ai_chat_enabled,
            ]);

            // Si se habilita por primera vez, establecer fecha de reset
            if ($request->ai_chat_enabled && !$empresa->ai_usage_reset_date) {
                $empresa->update([
                    'ai_usage_reset_date' => now()->addMonth()->startOfDay(),
                ]);
            }

            $action = $request->ai_chat_enabled ? 'habilitado' : 'deshabilitado';

            return response()->json([
                'success' => true,
                'message' => "AI Chat {$action} correctamente para {$empresa->nombre}",
                'data' => [
                    'empresa_id' => $empresa->id,
                    'empresa_nombre' => $empresa->nombre,
                    'ai_chat_enabled' => $empresa->ai_chat_enabled,
                    'ai_usage_reset_date' => $empresa->ai_usage_reset_date,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado de AI',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas globales de uso de AI
     */
    public function getGlobalStats()
    {
        try {
            $stats = [
                'total_empresas' => Empresa::count(),
                'empresas_con_ai_habilitado' => Empresa::where('ai_chat_enabled', true)->count(),
                'empresas_con_api_key_propia' => Empresa::whereNotNull('openai_api_key')->count(),
                'total_queries_mes_actual' => Empresa::where('ai_chat_enabled', true)->sum('ai_total_queries'),
                'uso_total_mes_actual' => Empresa::where('ai_chat_enabled', true)->sum('ai_monthly_usage'),
                'presupuesto_total_mes_actual' => Empresa::where('ai_chat_enabled', true)->sum('ai_monthly_budget'),
            ];

            // Estadísticas por modo de detección
            $statsByMode = Empresa::where('ai_chat_enabled', true)
                ->select('ai_detection_mode', DB::raw('COUNT(*) as count'))
                ->groupBy('ai_detection_mode')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->ai_detection_mode => $item->count];
                });

            $stats['empresas_por_modo'] = [
                'regex' => $statsByMode['regex'] ?? 0,
                'function_calling' => $statsByMode['function_calling'] ?? 0,
                'double_call' => $statsByMode['double_call'] ?? 0,
            ];

            // Top 10 empresas por uso
            $topEmpresas = Empresa::where('ai_chat_enabled', true)
                ->orderBy('ai_monthly_usage', 'desc')
                ->limit(10)
                ->get(['id', 'nombre', 'ai_monthly_usage', 'ai_total_queries', 'ai_detection_mode']);

            $stats['top_empresas_por_uso'] = $topEmpresas;

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas globales',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resetear el uso mensual de una empresa
     */
    public function resetMonthlyUsage(Request $request, $empresaId)
    {
        try {
            $empresa = Empresa::findOrFail($empresaId);

            $usageBeforeReset = $empresa->ai_monthly_usage;

            $empresa->update([
                'ai_monthly_usage' => 0.00,
                'ai_usage_reset_date' => now()->addMonth()->startOfDay(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Uso mensual reseteado para {$empresa->nombre}",
                'data' => [
                    'empresa_id' => $empresa->id,
                    'empresa_nombre' => $empresa->nombre,
                    'usage_before_reset' => $usageBeforeReset,
                    'current_usage' => $empresa->ai_monthly_usage,
                    'next_reset_date' => $empresa->ai_usage_reset_date,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al resetear uso mensual',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar configuración de AI de una empresa (como SuperAdmin)
     */
    public function updateCompanyConfig(Request $request, $empresaId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ai_chat_enabled' => 'nullable|boolean',
                'ai_detection_mode' => 'nullable|in:regex,function_calling,double_call',
                'ai_monthly_budget' => 'nullable|numeric|min:0',
                'openai_model' => 'nullable|string|max:50',
                'openai_max_tokens' => 'nullable|integer|min:100|max:4000',
                'openai_temperature' => 'nullable|numeric|min:0|max:2',
            ], [
                'ai_detection_mode.in' => 'El modo de detección debe ser: regex, function_calling o double_call',
                'openai_max_tokens.min' => 'El mínimo de tokens es 100',
                'openai_max_tokens.max' => 'El máximo de tokens es 4000',
                'openai_temperature.min' => 'La temperatura mínima es 0',
                'openai_temperature.max' => 'La temperatura máxima es 2',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $empresa = Empresa::findOrFail($empresaId);

            // Actualizar solo los campos enviados
            $updateData = array_filter([
                'ai_chat_enabled' => $request->ai_chat_enabled ?? $empresa->ai_chat_enabled,
                'ai_detection_mode' => $request->ai_detection_mode ?? $empresa->ai_detection_mode,
                'ai_monthly_budget' => $request->ai_monthly_budget ?? $empresa->ai_monthly_budget,
                'openai_model' => $request->openai_model ?? $empresa->openai_model,
                'openai_max_tokens' => $request->openai_max_tokens ?? $empresa->openai_max_tokens,
                'openai_temperature' => $request->openai_temperature ?? $empresa->openai_temperature,
            ]);

            $empresa->update($updateData);

            return response()->json([
                'success' => true,
                'message' => "Configuración de AI actualizada para {$empresa->nombre}",
                'data' => [
                    'empresa_id' => $empresa->id,
                    'empresa_nombre' => $empresa->nombre,
                    'ai_chat_enabled' => $empresa->ai_chat_enabled,
                    'ai_detection_mode' => $empresa->ai_detection_mode,
                    'ai_monthly_budget' => $empresa->ai_monthly_budget,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ver detalle completo de configuración AI de una empresa
     */
    public function showCompanyConfig($empresaId)
    {
        try {
            $empresa = Empresa::with('users')->findOrFail($empresaId);

            $usersWithAIPermission = $empresa->users->filter(function ($user) {
                return $user->hasPermissionTo('use-ai-chat');
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'empresa' => [
                        'id' => $empresa->id,
                        'nombre' => $empresa->nombre,
                        'email' => $empresa->email,
                        'activo' => $empresa->activo,
                    ],
                    'ai_config' => [
                        'ai_chat_enabled' => $empresa->ai_chat_enabled,
                        'ai_detection_mode' => $empresa->ai_detection_mode,
                        'openai_model' => $empresa->openai_model,
                        'openai_max_tokens' => $empresa->openai_max_tokens,
                        'openai_temperature' => $empresa->openai_temperature,
                        'has_own_api_key' => !empty($empresa->openai_api_key),
                    ],
                    'budget' => [
                        'ai_monthly_budget' => $empresa->ai_monthly_budget,
                        'ai_monthly_usage' => $empresa->ai_monthly_usage,
                        'usage_percentage' => $empresa->ai_monthly_budget
                            ? round(($empresa->ai_monthly_usage / $empresa->ai_monthly_budget) * 100, 2)
                            : 0,
                        'ai_usage_reset_date' => $empresa->ai_usage_reset_date,
                    ],
                    'stats' => [
                        'ai_total_queries' => $empresa->ai_total_queries,
                        'ai_last_used_at' => $empresa->ai_last_used_at,
                        'total_users' => $empresa->users->count(),
                        'users_with_ai_permission' => $usersWithAIPermission->count(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración de empresa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener información de planes y precios
     */
    public function getPlansInfo()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'plans' => [
                    [
                        'mode' => 'regex',
                        'name' => 'Plan Básico - Regex',
                        'description' => 'Detección de intención con expresiones regulares (PHP)',
                        'cost_per_query' => 'Bajo (~$0.002 - $0.005)',
                        'openai_calls' => 1,
                        'recommended_for' => 'Empresas con consultas predecibles y presupuesto limitado',
                        'color' => 'green',
                    ],
                    [
                        'mode' => 'function_calling',
                        'name' => 'Plan Intermedio - Function Calling',
                        'description' => 'OpenAI decide automáticamente qué funciones ejecutar',
                        'cost_per_query' => 'Medio (~$0.005 - $0.010)',
                        'openai_calls' => 1,
                        'recommended_for' => 'Balance entre costo e inteligencia',
                        'color' => 'blue',
                    ],
                    [
                        'mode' => 'double_call',
                        'name' => 'Plan Premium - Double Call',
                        'description' => 'Dos llamadas: detección de intención + respuesta personalizada',
                        'cost_per_query' => 'Alto (~$0.010 - $0.020)',
                        'openai_calls' => 2,
                        'recommended_for' => 'Máxima precisión y calidad',
                        'color' => 'purple',
                    ],
                ],
                'pricing_note' => 'Los costos son estimados y varían según el modelo de OpenAI usado (gpt-3.5-turbo, gpt-4, etc.) y la cantidad de tokens por consulta.',
            ],
        ]);
    }
}
