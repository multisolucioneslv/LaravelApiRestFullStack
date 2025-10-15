<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empresa\UpdateEmpresaConfigRequest;
use App\Http\Resources\EmpresaResource;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador para la configuración de la empresa del usuario autenticado
 *
 * Este controlador es para que los Admins gestionen su propia empresa
 * NO es para el CRUD completo de empresas (eso es solo para SuperAdmin)
 */
class EmpresaConfiguracionController extends Controller
{
    /**
     * Obtener la configuración de la empresa del usuario autenticado
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Verificar que el usuario tenga una empresa asignada
            if (!$user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene una empresa asignada'
                ], 404);
            }

            // Cargar la empresa con sus relaciones
            $empresa = Empresa::with(['telefono', 'moneda', 'users'])
                ->findOrFail($user->empresa_id);

            return response()->json([
                'success' => true,
                'data' => new EmpresaResource($empresa)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración de la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar la configuración de la empresa del usuario autenticado
     *
     * @param UpdateEmpresaConfigRequest $request
     * @return JsonResponse
     */
    public function update(UpdateEmpresaConfigRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            // Verificar que el usuario tenga una empresa asignada
            if (!$user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene una empresa asignada'
                ], 404);
            }

            $empresa = Empresa::findOrFail($user->empresa_id);

            // Preparar los datos para actualizar
            $data = [
                'nombre' => $request->nombre,
                'email' => $request->email,
                'direccion' => $request->direccion,
                'zona_horaria' => $request->zona_horaria,
                'horarios' => $request->horarios, // JSON field
                'moneda_id' => $request->moneda_id,
            ];

            // Manejo del logo
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior si existe
                if ($empresa->logo) {
                    Storage::disk('public')->delete($empresa->logo);
                }

                // Guardar nuevo logo
                $logoPath = $request->file('logo')->store('logos', 'public');
                $data['logo'] = $logoPath;
            }

            // Manejo del favicon
            if ($request->hasFile('favicon')) {
                // Eliminar favicon anterior si existe
                if ($empresa->favicon) {
                    Storage::disk('public')->delete($empresa->favicon);
                }

                // Guardar nuevo favicon
                $faviconPath = $request->file('favicon')->store('favicons', 'public');
                $data['favicon'] = $faviconPath;
            }

            // Manejo del fondo de login
            if ($request->hasFile('fondo_login')) {
                // Eliminar fondo anterior si existe
                if ($empresa->fondo_login) {
                    Storage::disk('public')->delete($empresa->fondo_login);
                }

                // Guardar nuevo fondo
                $fondoPath = $request->file('fondo_login')->store('fondos', 'public');
                $data['fondo_login'] = $fondoPath;
            }

            // Actualizar la empresa
            $empresa->update($data);

            // Recargar con relaciones
            $empresa->load(['telefono', 'moneda', 'users']);

            return response()->json([
                'success' => true,
                'message' => 'Configuración de empresa actualizada exitosamente',
                'data' => new EmpresaResource($empresa)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración de la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar el logo de la empresa
     *
     * @return JsonResponse
     */
    public function deleteLogo(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene una empresa asignada'
                ], 404);
            }

            $empresa = Empresa::findOrFail($user->empresa_id);

            if ($empresa->logo) {
                Storage::disk('public')->delete($empresa->logo);
                $empresa->update(['logo' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logo eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar el favicon de la empresa
     *
     * @return JsonResponse
     */
    public function deleteFavicon(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene una empresa asignada'
                ], 404);
            }

            $empresa = Empresa::findOrFail($user->empresa_id);

            if ($empresa->favicon) {
                Storage::disk('public')->delete($empresa->favicon);
                $empresa->update(['favicon' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Favicon eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el favicon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar el fondo de login de la empresa
     *
     * @return JsonResponse
     */
    public function deleteFondoLogin(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->empresa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene una empresa asignada'
                ], 404);
            }

            $empresa = Empresa::findOrFail($user->empresa_id);

            if ($empresa->fondo_login) {
                Storage::disk('public')->delete($empresa->fondo_login);
                $empresa->update(['fondo_login' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Fondo de login eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el fondo de login',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
