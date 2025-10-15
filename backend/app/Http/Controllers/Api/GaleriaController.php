<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Galeria\StoreGaleriaRequest;
use App\Http\Requests\Galeria\UpdateGaleriaRequest;
use App\Http\Resources\GaleriaResource;
use App\Models\Galeria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class GaleriaController extends Controller
{
    /**
     * Listar galerías con paginación y búsqueda
     * Búsqueda por: nombre
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $galerias = Galeria::query()
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => GaleriaResource::collection($galerias),
            'meta' => [
                'current_page' => $galerias->currentPage(),
                'last_page' => $galerias->lastPage(),
                'per_page' => $galerias->perPage(),
                'total' => $galerias->total(),
            ],
        ]);
    }

    /**
     * Crear nueva galería con múltiples imágenes
     */
    public function store(StoreGaleriaRequest $request): JsonResponse
    {
        $data = [
            'nombre' => $request->nombre,
            'galeriable_type' => $request->galeriable_type,
            'galeriable_id' => $request->galeriable_id,
            'imagenes' => [],
        ];

        // Procesar múltiples imágenes si existen
        if ($request->hasFile('imagenes')) {
            $imagenesArray = [];
            $files = $request->file('imagenes');

            foreach ($files as $index => $file) {
                $extension = $file->getClientOriginalExtension();

                // Generar nombre único para la imagen
                $filename = str_replace(' ', '_', $request->nombre)
                    . '_' . ($index + 1)
                    . '_' . microtime(true)
                    . '.' . $extension;

                // Guardar en storage/app/public/galerias/
                $path = $file->storeAs('galerias', $filename, 'public');

                // Agregar al array de imágenes
                $imagenesArray[] = [
                    'url' => $path,
                    'orden' => $index,
                    'es_principal' => $index === 0, // La primera imagen es principal
                ];
            }

            $data['imagenes'] = $imagenesArray;
        }

        $galeria = Galeria::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Galería creada exitosamente',
            'data' => new GaleriaResource($galeria),
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar una galería específica
     */
    public function show(int $id): JsonResponse
    {
        $galeria = Galeria::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new GaleriaResource($galeria),
        ]);
    }

    /**
     * Actualizar galería
     */
    public function update(UpdateGaleriaRequest $request, int $id): JsonResponse
    {
        $galeria = Galeria::findOrFail($id);

        $data = [
            'nombre' => $request->nombre,
            'galeriable_type' => $request->galeriable_type,
            'galeriable_id' => $request->galeriable_id,
        ];

        // Si se envían nuevas imágenes, eliminar las anteriores y subir las nuevas
        if ($request->hasFile('imagenes')) {
            // Eliminar imágenes anteriores del storage
            if ($galeria->imagenes && count($galeria->imagenes) > 0) {
                foreach ($galeria->imagenes as $imagen) {
                    if (isset($imagen['url']) && Storage::disk('public')->exists($imagen['url'])) {
                        Storage::disk('public')->delete($imagen['url']);
                    }
                }
            }

            // Procesar nuevas imágenes
            $imagenesArray = [];
            $files = $request->file('imagenes');

            foreach ($files as $index => $file) {
                $extension = $file->getClientOriginalExtension();

                $filename = str_replace(' ', '_', $request->nombre)
                    . '_' . ($index + 1)
                    . '_' . microtime(true)
                    . '.' . $extension;

                $path = $file->storeAs('galerias', $filename, 'public');

                $imagenesArray[] = [
                    'url' => $path,
                    'orden' => $index,
                    'es_principal' => $index === 0,
                ];
            }

            $data['imagenes'] = $imagenesArray;
        }

        $galeria->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Galería actualizada exitosamente',
            'data' => new GaleriaResource($galeria),
        ]);
    }

    /**
     * Eliminar una galería
     */
    public function destroy(int $id): JsonResponse
    {
        $galeria = Galeria::findOrFail($id);

        // Eliminar todas las imágenes del storage antes de eliminar el registro
        if ($galeria->imagenes && count($galeria->imagenes) > 0) {
            foreach ($galeria->imagenes as $imagen) {
                if (isset($imagen['url']) && Storage::disk('public')->exists($imagen['url'])) {
                    Storage::disk('public')->delete($imagen['url']);
                }
            }
        }

        $galeria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Galería eliminada exitosamente',
        ]);
    }

    /**
     * Eliminar múltiples galerías (por lotes)
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:galerias,id',
        ]);

        $ids = $request->input('ids');

        // Eliminar imágenes de cada galería antes de eliminar los registros
        $galerias = Galeria::whereIn('id', $ids)->get();
        foreach ($galerias as $galeria) {
            if ($galeria->imagenes && count($galeria->imagenes) > 0) {
                foreach ($galeria->imagenes as $imagen) {
                    if (isset($imagen['url']) && Storage::disk('public')->exists($imagen['url'])) {
                        Storage::disk('public')->delete($imagen['url']);
                    }
                }
            }
        }

        $deleted = Galeria::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deleted} galería(s) exitosamente",
            'deleted_count' => $deleted,
        ]);
    }
}
