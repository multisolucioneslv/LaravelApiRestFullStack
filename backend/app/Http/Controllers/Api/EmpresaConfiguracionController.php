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
            $empresa = Empresa::with(['phone', 'currency', 'users'])
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
                'currency_id' => $request->currency_id,
                'show_loading_effect' => $request->input('show_loading_effect', true),
            ];

            // Manejo del logo
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior si existe
                if ($empresa->logo) {
                    Storage::disk('public')->delete($empresa->logo);
                }

                // Generar nombre profesional para el archivo
                $file = $request->file('logo');
                $extension = $file->getClientOriginalExtension();
                $empresaSlug = $this->sanitizeFilename($empresa->nombre);
                $timestamp = str_replace('.', '', microtime(true));
                $filename = "{$empresaSlug}_logo_{$timestamp}.{$extension}";

                // Guardar con nombre personalizado
                $logoPath = $file->storeAs('logos', $filename, 'public');
                $data['logo'] = $logoPath;
            }

            // Manejo del favicon
            if ($request->hasFile('favicon')) {
                // Eliminar favicon anterior si existe
                if ($empresa->favicon) {
                    Storage::disk('public')->delete($empresa->favicon);
                }

                // Generar nombre profesional para el archivo
                $file = $request->file('favicon');
                $extension = $file->getClientOriginalExtension();
                $empresaSlug = $this->sanitizeFilename($empresa->nombre);
                $timestamp = str_replace('.', '', microtime(true));
                $filename = "{$empresaSlug}_favicon_{$timestamp}.{$extension}";

                // Guardar con nombre personalizado
                $faviconPath = $file->storeAs('favicons', $filename, 'public');
                $data['favicon'] = $faviconPath;
            }

            // Manejo del fondo de login
            if ($request->hasFile('fondo_login')) {
                // Eliminar fondo anterior si existe
                if ($empresa->fondo_login) {
                    Storage::disk('public')->delete($empresa->fondo_login);
                }

                // Generar nombre profesional para el archivo
                $file = $request->file('fondo_login');
                $extension = $file->getClientOriginalExtension();
                $empresaSlug = $this->sanitizeFilename($empresa->nombre);
                $timestamp = str_replace('.', '', microtime(true));
                $filename = "{$empresaSlug}_fondo_login_{$timestamp}.{$extension}";

                // Guardar con nombre personalizado
                $fondoPath = $file->storeAs('fondos', $filename, 'public');
                $data['fondo_login'] = $fondoPath;
            }

            // Actualizar la empresa
            $empresa->update($data);

            // Recargar con relaciones
            $empresa->load(['phone', 'currency', 'users']);

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

    /**
     * Sanitizar el nombre de archivo para hacerlo seguro y profesional
     *
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // Convertir a minúsculas
        $filename = mb_strtolower($filename, 'UTF-8');

        // Reemplazar caracteres especiales con equivalentes ASCII
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        ];
        $filename = strtr($filename, $replacements);

        // Reemplazar espacios y caracteres no alfanuméricos con guiones bajos
        $filename = preg_replace('/[^a-z0-9]+/', '_', $filename);

        // Eliminar guiones bajos múltiples consecutivos
        $filename = preg_replace('/_+/', '_', $filename);

        // Eliminar guiones bajos al inicio y al final
        $filename = trim($filename, '_');

        // Limitar longitud a 50 caracteres
        $filename = substr($filename, 0, 50);

        // Si queda vacío después de sanitizar, usar el ID
        if (empty($filename)) {
            $filename = 'empresa_' . auth()->user()->empresa_id;
        }

        return $filename;
    }
}
