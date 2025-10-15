<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Middleware para verificar permisos usando Spatie Laravel Permission
     *
     * Uso: Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.index');
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  El permiso requerido
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Verificar que el usuario está autenticado
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = auth()->user();

        // SuperAdmin tiene todos los permisos automáticamente
        if ($user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso específico
        if (!$user->hasPermissionTo($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción',
                'required_permission' => $permission,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
