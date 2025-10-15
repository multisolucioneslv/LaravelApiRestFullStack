<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToEmpresa
{
    /**
     * Handle an incoming request.
     *
     * Valida que los usuarios solo puedan acceder a datos de su propia empresa.
     * SuperAdmin tiene acceso a todas las empresas.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        // Si no hay usuario autenticado, continuar (otros middlewares manejarán esto)
        if (!$user) {
            return $next($request);
        }

        // Si el usuario no tiene empresa_id asignada, denegar acceso
        if (!$user->empresa_id) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no tiene empresa asignada. Contacte al administrador.'
            ], Response::HTTP_FORBIDDEN);
        }

        // SuperAdmin tiene acceso a todas las empresas (bypass)
        if ($user->hasRole('SuperAdmin')) {
            return $next($request);
        }

        // Para usuarios normales, agregar empresa_id a la request para filtrado
        $request->merge(['empresa_id' => $user->empresa_id]);

        return $next($request);
    }
}
