<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:productos,sku',
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'nullable|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'stock_actual' => 'required|integer|min:0',
            'unidad_medida' => 'required|string|max:50',
            'imagen' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
            'empresa_id' => 'required|exists:empresas,id',
            'categorias' => 'nullable|array',
            'categorias.*' => 'exists:categorias,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.max' => 'El nombre no puede exceder los :max caracteres.',
            'sku.required' => 'El campo SKU es requerido.',
            'sku.max' => 'El SKU no puede exceder los :max caracteres.',
            'sku.unique' => 'Este SKU ya está registrado.',
            'codigo_barras.max' => 'El código de barras no puede exceder los :max caracteres.',
            'codigo_barras.unique' => 'Este código de barras ya está registrado.',
            'precio_compra.required' => 'El precio de compra es requerido.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra.min' => 'El precio de compra debe ser mayor o igual a :min.',
            'precio_venta.required' => 'El precio de venta es requerido.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta debe ser mayor o igual a :min.',
            'precio_mayoreo.numeric' => 'El precio de mayoreo debe ser un número.',
            'precio_mayoreo.min' => 'El precio de mayoreo debe ser mayor o igual a :min.',
            'stock_minimo.required' => 'El stock mínimo es requerido.',
            'stock_minimo.integer' => 'El stock mínimo debe ser un número entero.',
            'stock_minimo.min' => 'El stock mínimo debe ser mayor o igual a :min.',
            'stock_actual.required' => 'El stock actual es requerido.',
            'stock_actual.integer' => 'El stock actual debe ser un número entero.',
            'stock_actual.min' => 'El stock actual debe ser mayor o igual a :min.',
            'unidad_medida.required' => 'La unidad de medida es requerida.',
            'unidad_medida.max' => 'La unidad de medida no puede exceder los :max caracteres.',
            'empresa_id.required' => 'El campo empresa es requerido.',
            'empresa_id.exists' => 'La empresa seleccionada no es válida.',
            'categorias.array' => 'Las categorías deben ser un arreglo.',
            'categorias.*.exists' => 'Una o más categorías seleccionadas no son válidas.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
