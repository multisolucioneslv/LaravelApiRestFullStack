<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanUseAIChat
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Verificar que el usuario esté autenticado
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        // Verificar que la empresa tenga AI Chat habilitado
        if (!$user->empresa || !$user->empresa->ai_chat_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'El servicio de AI Chat no está habilitado para tu empresa. Contacta al administrador.',
                'code' => 'AI_NOT_ENABLED_FOR_COMPANY',
            ], 403);
        }

        // Verificar que el usuario tenga el permiso
        if (!$user->hasPermissionTo('use-ai-chat')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para usar AI Chat. Contacta al administrador de tu empresa.',
                'code' => 'AI_PERMISSION_DENIED',
            ], 403);
        }

        // Verificar presupuesto mensual si está configurado
        $empresa = $user->empresa;
        if ($empresa->ai_monthly_budget && $empresa->ai_monthly_usage >= $empresa->ai_monthly_budget) {
            return response()->json([
                'success' => false,
                'message' => 'Tu empresa ha alcanzado el límite de uso mensual de AI Chat. Contacta al administrador.',
                'code' => 'AI_BUDGET_EXCEEDED',
            ], 403);
        }

        return $next($request);
    }
}
