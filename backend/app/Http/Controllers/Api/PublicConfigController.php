<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;

/**
 * Controlador para endpoints públicos (sin autenticación)
 * Usado para obtener configuración visible en páginas públicas como Login
 */
class PublicConfigController extends Controller
{
    /**
     * Obtener configuración pública de la empresa principal
     *
     * Este endpoint NO requiere autenticación
     * Devuelve logo, favicon y fondo de login de la primera empresa activa
     *
     * @return JsonResponse
     */
    public function getLoginConfig(): JsonResponse
    {
        try {
            // Obtener la primera empresa activa (puedes ajustar la lógica según tu necesidad)
            // Por ejemplo, si tienes un dominio específico, buscar por dominio
            $empresa = Empresa::where('activo', true)
                ->orderBy('id', 'asc')
                ->first();

            if (!$empresa) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'logo' => null,
                        'favicon' => null,
                        'fondo_login' => null,
                        'nombre' => 'Sistema',
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'nombre' => $empresa->nombre,
                    'logo' => $empresa->logo ? asset('storage/' . $empresa->logo) : null,
                    'favicon' => $empresa->favicon ? asset('storage/' . $empresa->favicon) : null,
                    'fondo_login' => $empresa->fondo_login ? asset('storage/' . $empresa->fondo_login) : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
