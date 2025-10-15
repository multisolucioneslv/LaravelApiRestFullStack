<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empresa\StoreEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaRequest;
use App\Http\Resources\EmpresaResource;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class EmpresaController extends Controller
{
    /**
     * Listar empresas con paginación y búsqueda
     * Búsqueda por: nombre, email
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $empresas = Empresa::query()
            ->with(['telefono', 'moneda'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => EmpresaResource::collection($empresas),
            'meta' => [
                'current_page' => $empresas->currentPage(),
                'last_page' => $empresas->lastPage(),
                'per_page' => $empresas->perPage(),
                'total' => $empresas->total(),
            ],
        ]);
    }

    /**
     * Crear nueva empresa
     */
    public function store(StoreEmpresaRequest $request): JsonResponse
    {
        $data = [
            'nombre' => $request->nombre,
            'telefono_id' => $request->telefono_id,
            'moneda_id' => $request->moneda_id,
            'email' => $request->email,
            'direccion' => $request->direccion,
            'zona_horaria' => $request->input('zona_horaria', 'America/Los_Angeles'),
            'activo' => $request->input('activo', true),
        ];

        // Manejar upload de logo
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $extension = $logo->getClientOriginalExtension();

            // Guardar con el nombre de la empresa + microtime para evitar caché
            $filename = str_replace(' ', '_', $request->nombre) . '_logo.' . microtime(true) . '.' . $extension;
            $path = $logo->storeAs('empresas/logos', $filename, 'public');
            $data['logo'] = $path;
        }

        // Manejar upload de favicon
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            $extension = $favicon->getClientOriginalExtension();

            $filename = str_replace(' ', '_', $request->nombre) . '_favicon.' . microtime(true) . '.' . $extension;
            $path = $favicon->storeAs('empresas/favicons', $filename, 'public');
            $data['favicon'] = $path;
        }

        // Manejar upload de fondo_login
        if ($request->hasFile('fondo_login')) {
            $fondo = $request->file('fondo_login');
            $extension = $fondo->getClientOriginalExtension();

            $filename = str_replace(' ', '_', $request->nombre) . '_fondo.' . microtime(true) . '.' . $extension;
            $path = $fondo->storeAs('empresas/fondos', $filename, 'public');
            $data['fondo_login'] = $path;
        }

        $empresa = Empresa::create($data);
        $empresa->load(['telefono', 'moneda']);

        return response()->json([
            'success' => true,
            'message' => 'Empresa creada exitosamente',
            'data' => new EmpresaResource($empresa),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar una empresa específica
     */
    public function show(int $id): JsonResponse
    {
        $empresa = Empresa::with(['telefono', 'moneda'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new EmpresaResource($empresa),
        ]);
    }

    /**
     * Actualizar empresa
     */
    public function update(UpdateEmpresaRequest $request, int $id): JsonResponse
    {
        $empresa = Empresa::findOrFail($id);

        $data = [
            'nombre' => $request->nombre,
            'telefono_id' => $request->telefono_id,
            'moneda_id' => $request->moneda_id,
            'email' => $request->email,
            'direccion' => $request->direccion,
            'zona_horaria' => $request->zona_horaria,
            'activo' => $request->activo,
        ];

        // Manejar upload de logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($empresa->logo && Storage::disk('public')->exists($empresa->logo)) {
                Storage::disk('public')->delete($empresa->logo);
            }

            $logo = $request->file('logo');
            $extension = $logo->getClientOriginalExtension();

            $filename = str_replace(' ', '_', $request->nombre) . '_logo.' . microtime(true) . '.' . $extension;
            $path = $logo->storeAs('empresas/logos', $filename, 'public');
            $data['logo'] = $path;
        }

        // Manejar upload de favicon
        if ($request->hasFile('favicon')) {
            // Eliminar favicon anterior si existe
            if ($empresa->favicon && Storage::disk('public')->exists($empresa->favicon)) {
                Storage::disk('public')->delete($empresa->favicon);
            }

            $favicon = $request->file('favicon');
            $extension = $favicon->getClientOriginalExtension();

            $filename = str_replace(' ', '_', $request->nombre) . '_favicon.' . microtime(true) . '.' . $extension;
            $path = $favicon->storeAs('empresas/favicons', $filename, 'public');
            $data['favicon'] = $path;
        }

        // Manejar upload de fondo_login
        if ($request->hasFile('fondo_login')) {
            // Eliminar fondo anterior si existe
            if ($empresa->fondo_login && Storage::disk('public')->exists($empresa->fondo_login)) {
                Storage::disk('public')->delete($empresa->fondo_login);
            }

            $fondo = $request->file('fondo_login');
            $extension = $fondo->getClientOriginalExtension();

            $filename = str_replace(' ', '_', $request->nombre) . '_fondo.' . microtime(true) . '.' . $extension;
            $path = $fondo->storeAs('empresas/fondos', $filename, 'public');
            $data['fondo_login'] = $path;
        }

        $empresa->update($data);
        $empresa->load(['telefono', 'moneda']);

        return response()->json([
            'success' => true,
            'message' => 'Empresa actualizada exitosamente',
            'data' => new EmpresaResource($empresa),
        ]);
    }

    /**
     * Eliminar una empresa
     */
    public function destroy(int $id): JsonResponse
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Empresa eliminada exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples empresas (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:empresas,id',
        ]);

        $ids = $request->input('ids');
        $deleted = Empresa::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} empresa(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
