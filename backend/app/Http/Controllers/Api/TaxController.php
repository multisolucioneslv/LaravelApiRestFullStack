<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tax\StoreTaxRequest;
use App\Http\Requests\Tax\UpdateTaxRequest;
use App\Http\Resources\TaxResource;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaxController extends Controller
{
    /**
     * Listar impuestos con paginación y búsqueda
     * Búsqueda por: nombre, descripcion
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search', '');

            $taxes = Tax::query()
                ->with('empresa:id,nombre') // Eager loading de la relación empresa
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                          ->orWhere('descripcion', 'like', "%{$search}%");
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => TaxResource::collection($taxes),
                'meta' => [
                    'current_page' => $taxes->currentPage(),
                    'last_page' => $taxes->lastPage(),
                    'per_page' => $taxes->perPage(),
                    'total' => $taxes->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar impuestos',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Crear nuevo impuesto
     */
    public function store(StoreTaxRequest $request): JsonResponse
    {
        try {
            $tax = Tax::create([
                'nombre' => $request->nombre,
                'porcentaje' => $request->porcentaje,
                'descripcion' => $request->descripcion,
                'empresa_id' => $request->empresa_id,
                'activo' => $request->input('activo', true),
            ]);

            // Cargar relación empresa
            $tax->load('empresa:id,nombre');

            return response()->json([
                'success' => true,
                'message' => 'Impuesto creado exitosamente',
                'data' => new TaxResource($tax),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear impuesto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar un impuesto específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $tax = Tax::with('empresa:id,nombre')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new TaxResource($tax),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar impuesto',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Actualizar impuesto
     */
    public function update(UpdateTaxRequest $request, int $id): JsonResponse
    {
        try {
            $tax = Tax::findOrFail($id);

            $tax->update([
                'nombre' => $request->nombre,
                'porcentaje' => $request->porcentaje,
                'descripcion' => $request->descripcion,
                'empresa_id' => $request->empresa_id,
                'activo' => $request->activo,
            ]);

            // Cargar relación empresa
            $tax->load('empresa:id,nombre');

            return response()->json([
                'success' => true,
                'message' => 'Impuesto actualizado exitosamente',
                'data' => new TaxResource($tax),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar impuesto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar un impuesto
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $tax = Tax::findOrFail($id);
            $tax->delete();

            return response()->json([
                'success' => true,
                'message' => 'Impuesto eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar impuesto',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar múltiples impuestos (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:taxes,id',
            ]);

            $ids = $request->input('ids');
            $deleted = Tax::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$deleted} impuesto(s) exitosamente",
                'deleted_count' => $deleted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar impuestos',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
