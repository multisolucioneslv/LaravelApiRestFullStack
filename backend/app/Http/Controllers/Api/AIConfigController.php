<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AIConfigController extends Controller
{
    /**
     * Obtener configuración actual de AI Chat de la empresa
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $empresa = $user->empresa;

            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la empresa del usuario',
                ], 404);
            }

            // Verificar que el usuario sea administrador
            if (!$user->hasRole('Administrador') && !$user->hasRole('SuperAdmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los administradores pueden ver la configuración de AI',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'ai_chat_enabled' => $empresa->ai_chat_enabled,
                    'ai_detection_mode' => $empresa->ai_detection_mode,
                    'openai_model' => $empresa->openai_model,
                    'openai_max_tokens' => $empresa->openai_max_tokens,
                    'openai_temperature' => $empresa->openai_temperature,
                    'ai_monthly_budget' => $empresa->ai_monthly_budget,
                    'ai_monthly_usage' => $empresa->ai_monthly_usage,
                    'ai_usage_reset_date' => $empresa->ai_usage_reset_date,
                    'ai_total_queries' => $empresa->ai_total_queries,
                    'ai_last_used_at' => $empresa->ai_last_used_at,
                    'has_own_api_key' => !empty($empresa->openai_api_key),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración de AI',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar configuración de AI Chat de la empresa
     */
    public function update(Request $request)
    {
        try {
            $user = auth()->user();
            $empresa = $user->empresa;

            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la empresa del usuario',
                ], 404);
            }

            // Verificar que el usuario sea administrador
            if (!$user->hasRole('Administrador') && !$user->hasRole('SuperAdmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los administradores pueden modificar la configuración de AI',
                ], 403);
            }

            // Validación
            $validator = Validator::make($request->all(), [
                'ai_detection_mode' => 'nullable|in:regex,function_calling,double_call',
                'openai_api_key' => 'nullable|string|min:20',
                'openai_model' => 'nullable|string|max:50',
                'openai_max_tokens' => 'nullable|integer|min:100|max:4000',
                'openai_temperature' => 'nullable|numeric|min:0|max:2',
                'ai_monthly_budget' => 'nullable|numeric|min:0',
            ], [
                'ai_detection_mode.in' => 'El modo de detección debe ser: regex, function_calling o double_call',
                'openai_api_key.min' => 'La API Key de OpenAI debe tener al menos 20 caracteres',
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

            // Actualizar solo los campos enviados
            $updateData = [];

            if ($request->has('ai_detection_mode')) {
                $updateData['ai_detection_mode'] = $request->ai_detection_mode;
            }

            if ($request->has('openai_api_key')) {
                $updateData['openai_api_key'] = $request->openai_api_key;
            }

            if ($request->has('openai_model')) {
                $updateData['openai_model'] = $request->openai_model;
            }

            if ($request->has('openai_max_tokens')) {
                $updateData['openai_max_tokens'] = $request->openai_max_tokens;
            }

            if ($request->has('openai_temperature')) {
                $updateData['openai_temperature'] = $request->openai_temperature;
            }

            if ($request->has('ai_monthly_budget')) {
                $updateData['ai_monthly_budget'] = $request->ai_monthly_budget;
            }

            // Actualizar empresa
            $empresa->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Configuración de AI actualizada correctamente',
                'data' => [
                    'ai_detection_mode' => $empresa->ai_detection_mode,
                    'openai_model' => $empresa->openai_model,
                    'openai_max_tokens' => $empresa->openai_max_tokens,
                    'openai_temperature' => $empresa->openai_temperature,
                    'ai_monthly_budget' => $empresa->ai_monthly_budget,
                    'has_own_api_key' => !empty($empresa->openai_api_key),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración de AI',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener lista de usuarios de la empresa con su estado de permiso AI
     */
    public function getAvailableUsers(Request $request)
    {
        try {
            $user = auth()->user();
            $empresa = $user->empresa;

            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la empresa del usuario',
                ], 404);
            }

            // Verificar que el usuario sea administrador
            if (!$user->hasRole('Administrador') && !$user->hasRole('SuperAdmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los administradores pueden ver los usuarios',
                ], 403);
            }

            // Obtener usuarios de la empresa con sus permisos
            $users = User::where('empresa_id', $empresa->id)
                ->with('roles')
                ->get()
                ->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'roles' => $u->roles->pluck('name'),
                        'has_ai_permission' => $u->hasPermissionTo('use-ai-chat'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Asignar o remover permiso 'use-ai-chat' a usuarios específicos
     */
    public function updateUserPermissions(Request $request)
    {
        try {
            $user = auth()->user();
            $empresa = $user->empresa;

            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la empresa del usuario',
                ], 404);
            }

            // Verificar que el usuario sea administrador
            if (!$user->hasRole('Administrador') && !$user->hasRole('SuperAdmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los administradores pueden modificar permisos',
                ], 403);
            }

            // Validación
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'grant_permission' => 'required|boolean',
            ], [
                'user_id.required' => 'El ID del usuario es requerido',
                'user_id.exists' => 'El usuario no existe',
                'grant_permission.required' => 'Debe especificar si otorgar o revocar el permiso',
                'grant_permission.boolean' => 'El valor debe ser verdadero o falso',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Verificar que el usuario pertenezca a la misma empresa
            $targetUser = User::find($request->user_id);
            if ($targetUser->empresa_id !== $empresa->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no pertenece a tu empresa',
                ], 403);
            }

            // Asignar o remover permiso
            if ($request->grant_permission) {
                $targetUser->givePermissionTo('use-ai-chat');
                $message = 'Permiso de AI Chat otorgado correctamente';
            } else {
                $targetUser->revokePermissionTo('use-ai-chat');
                $message = 'Permiso de AI Chat revocado correctamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'user_id' => $targetUser->id,
                    'user_name' => $targetUser->name,
                    'has_ai_permission' => $targetUser->hasPermissionTo('use-ai-chat'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar permisos del usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de uso de AI Chat de la empresa
     */
    public function getUsageStats(Request $request)
    {
        try {
            $user = auth()->user();
            $empresa = $user->empresa;

            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la empresa del usuario',
                ], 404);
            }

            // Verificar que el usuario sea administrador
            if (!$user->hasRole('Administrador') && !$user->hasRole('SuperAdmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los administradores pueden ver las estadísticas',
                ], 403);
            }

            // Calcular porcentaje de uso si hay presupuesto definido
            $usagePercentage = 0;
            if ($empresa->ai_monthly_budget && $empresa->ai_monthly_budget > 0) {
                $usagePercentage = ($empresa->ai_monthly_usage / $empresa->ai_monthly_budget) * 100;
            }

            // Obtener cantidad de usuarios con permiso de AI
            $usersWithPermission = User::where('empresa_id', $empresa->id)
                ->permission('use-ai-chat')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'ai_monthly_budget' => $empresa->ai_monthly_budget,
                    'ai_monthly_usage' => $empresa->ai_monthly_usage,
                    'usage_percentage' => round($usagePercentage, 2),
                    'ai_usage_reset_date' => $empresa->ai_usage_reset_date,
                    'ai_total_queries' => $empresa->ai_total_queries,
                    'ai_last_used_at' => $empresa->ai_last_used_at,
                    'users_with_permission' => $usersWithPermission,
                    'detection_mode' => $empresa->ai_detection_mode,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de uso',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener información de los planes de AI disponibles
     */
    public function getAvailablePlans()
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'mode' => 'regex',
                    'name' => 'Plan Básico - Regex',
                    'description' => 'Usa expresiones regulares (PHP) para detectar la intención del usuario. Solo hace 1 llamada a OpenAI para generar la respuesta.',
                    'cost_estimate' => 'Bajo',
                    'pros' => [
                        'Más económico',
                        '1 sola llamada a OpenAI por consulta',
                        'Rápido y eficiente',
                        'Ideal para consultas predecibles',
                    ],
                    'cons' => [
                        'Menos flexible',
                        'Requiere patrones predefinidos',
                        'No entiende contextos complejos',
                    ],
                    'recommended_for' => 'Empresas con consultas estándar y presupuesto limitado',
                ],
                [
                    'mode' => 'function_calling',
                    'name' => 'Plan Intermedio - Function Calling',
                    'description' => 'OpenAI analiza la pregunta y decide automáticamente qué funciones del sistema llamar usando Function Calling.',
                    'cost_estimate' => 'Medio',
                    'pros' => [
                        'Más inteligente que regex',
                        'OpenAI decide qué funciones ejecutar',
                        'Flexible y adaptable',
                        '1 llamada optimizada a OpenAI',
                    ],
                    'cons' => [
                        'Más costoso que regex',
                        'Requiere tokens adicionales para function definitions',
                    ],
                    'recommended_for' => 'Empresas que buscan balance entre costo e inteligencia',
                ],
                [
                    'mode' => 'double_call',
                    'name' => 'Plan Premium - Double Call',
                    'description' => 'Hace 2 llamadas separadas a OpenAI: primero para detectar la intención y luego para generar la respuesta personalizada.',
                    'cost_estimate' => 'Alto',
                    'pros' => [
                        'Máxima precisión',
                        'Mejor comprensión del contexto',
                        'Respuestas más naturales',
                        'Ideal para casos complejos',
                    ],
                    'cons' => [
                        'El más costoso',
                        '2 llamadas a OpenAI por consulta',
                        'Mayor latencia',
                    ],
                    'recommended_for' => 'Empresas que priorizan calidad sobre costo',
                ],
            ],
        ]);
    }
}
