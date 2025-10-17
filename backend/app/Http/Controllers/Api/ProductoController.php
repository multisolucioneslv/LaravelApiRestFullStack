<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProductoController extends Controller
{
    /**
     * Listar productos con paginación y búsqueda
     * Búsqueda por: nombre, sku, codigo_barras
     * Orden: DESC por defecto
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');

        $productos = Producto::query()
            ->forCurrentUser() // Multi-tenancy: Solo productos de la empresa del usuario
            ->with(['empresa', 'categorias'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('codigo_barras', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $productos->items(),
            'meta' => [
                'current_page' => $productos->currentPage(),
                'last_page' => $productos->lastPage(),
                'per_page' => $productos->perPage(),
                'total' => $productos->total(),
            ],
        ]);
    }

    /**
     * Crear nuevo producto
     */
    public function store(Request $request): JsonResponse
    {
        // Validaciones
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:productos,sku',
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'stock_actual' => 'required|integer|min:0',
            'unidad_medida' => 'required|string|max:50',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'activo' => 'boolean',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ], [
            'nombre.required' => 'El nombre del producto es requerido',
            'sku.required' => 'El SKU es requerido',
            'sku.unique' => 'Este SKU ya está registrado',
            'precio_compra.required' => 'El precio de compra es requerido',
            'precio_venta.required' => 'El precio de venta es requerido',
            'stock_minimo.required' => 'El stock mínimo es requerido',
            'stock_actual.required' => 'El stock actual es requerido',
            'unidad_medida.required' => 'La unidad de medida es requerida',
            'imagen.image' => 'El archivo debe ser una imagen',
            'imagen.max' => 'La imagen no puede pesar más de 2MB',
        ]);

        $data = [
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'sku' => $validated['sku'],
            'codigo_barras' => $validated['codigo_barras'] ?? null,
            'precio_compra' => $validated['precio_compra'],
            'precio_venta' => $validated['precio_venta'],
            'precio_mayoreo' => $validated['precio_mayoreo'] ?? null,
            'stock_minimo' => $validated['stock_minimo'],
            'stock_actual' => $validated['stock_actual'],
            'unidad_medida' => $validated['unidad_medida'],
            'activo' => $validated['activo'] ?? true,
            'empresa_id' => auth()->user()->empresa_id, // Multi-tenancy
        ];

        // Manejar upload de imagen
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $extension = $imagen->getClientOriginalExtension();

            $skuSlug = $this->sanitizeFilename($validated['sku']);
            $timestamp = str_replace('.', '', microtime(true));
            $filename = "producto_{$skuSlug}_{$timestamp}.{$extension}";

            $path = $imagen->storeAs('productos', $filename, 'public');
            $data['imagen'] = $path;
        }

        $producto = Producto::create($data);

        // Asignar categorías si se proporcionan
        if (isset($validated['categorias'])) {
            $producto->categorias()->sync($validated['categorias']);
        }

        $producto->load(['empresa', 'categorias']);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => $producto,
        ], Response::HTTP_CREATED);
    }

    /**
     * Mostrar un producto específico
     */
    public function show(int $id): JsonResponse
    {
        $producto = Producto::with(['empresa', 'categorias', 'inventarios'])
            ->findOrFail($id);

        // Multi-tenancy: Validar acceso a la empresa
        $producto->validateEmpresaAccess($producto->empresa_id);

        return response()->json([
            'success' => true,
            'data' => $producto,
        ]);
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        // Multi-tenancy: Validar acceso a la empresa
        $producto->validateEmpresaAccess($producto->empresa_id);

        // Validaciones
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:productos,sku,' . $id,
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras,' . $id,
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'stock_actual' => 'required|integer|min:0',
            'unidad_medida' => 'required|string|max:50',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'activo' => 'boolean',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ]);

        $data = [
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'sku' => $validated['sku'],
            'codigo_barras' => $validated['codigo_barras'] ?? null,
            'precio_compra' => $validated['precio_compra'],
            'precio_venta' => $validated['precio_venta'],
            'precio_mayoreo' => $validated['precio_mayoreo'] ?? null,
            'stock_minimo' => $validated['stock_minimo'],
            'stock_actual' => $validated['stock_actual'],
            'unidad_medida' => $validated['unidad_medida'],
            'activo' => $validated['activo'] ?? $producto->activo,
        ];

        // Manejar upload de imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }

            $imagen = $request->file('imagen');
            $extension = $imagen->getClientOriginalExtension();

            $skuSlug = $this->sanitizeFilename($validated['sku']);
            $timestamp = str_replace('.', '', microtime(true));
            $filename = "producto_{$skuSlug}_{$timestamp}.{$extension}";

            $path = $imagen->storeAs('productos', $filename, 'public');
            $data['imagen'] = $path;
        }

        $producto->update($data);

        // Actualizar categorías si se proporcionan
        if (isset($validated['categorias'])) {
            $producto->categorias()->sync($validated['categorias']);
        }

        $producto->load(['empresa', 'categorias']);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'data' => $producto,
        ]);
    }

    /**
     * Eliminar un producto (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        // Multi-tenancy: Validar acceso a la empresa
        $producto->validateEmpresaAccess($producto->empresa_id);

        $producto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente',
        ]);
    }

    /**
     * Restaurar un producto eliminado
     */
    public function restore(int $id): JsonResponse
    {
        $producto = Producto::withTrashed()->findOrFail($id);

        // Multi-tenancy: Validar acceso a la empresa
        $producto->validateEmpresaAccess($producto->empresa_id);

        $producto->restore();

        return response()->json([
            'success' => true,
            'message' => 'Producto restaurado exitosamente',
            'data' => $producto,
        ]);
    }

    /**
     * Sanitizar el nombre de archivo para hacerlo seguro y profesional
     */
    private function sanitizeFilename(string $filename): string
    {
        $filename = mb_strtolower($filename, 'UTF-8');

        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u',
        ];
        $filename = strtr($filename, $replacements);

        $filename = preg_replace('/[^a-z0-9]+/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        $filename = trim($filename, '_');
        $filename = substr($filename, 0, 30);

        if (empty($filename)) {
            $filename = 'producto';
        }

        return $filename;
    }
}
