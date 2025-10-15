<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'codigo' => 'required|string|max:100|unique:inventarios,codigo',
            'descripcion' => 'nullable|string',
            'galeria_id' => 'nullable|exists:galerias,id',
            'bodega_id' => 'required|exists:bodegas,id',
            'empresa_id' => 'required|exists:empresas,id',
            'cantidad' => 'nullable|integer|min:0',
            'minimo' => 'nullable|integer|min:0',
            'maximo' => 'nullable|integer|min:0',
            'precio_compra' => 'nullable|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'precio_venta' => 'nullable|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.max' => 'El nombre no puede exceder los :max caracteres.',
            'codigo.required' => 'El campo código es requerido.',
            'codigo.max' => 'El código no puede exceder los :max caracteres.',
            'codigo.unique' => 'Este código ya está registrado.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'galeria_id.exists' => 'La galería seleccionada no es válida.',
            'bodega_id.required' => 'El campo bodega es requerido.',
            'bodega_id.exists' => 'La bodega seleccionada no es válida.',
            'empresa_id.required' => 'El campo empresa es requerido.',
            'empresa_id.exists' => 'La empresa seleccionada no es válida.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad no puede ser menor a :min.',
            'minimo.integer' => 'El mínimo debe ser un número entero.',
            'minimo.min' => 'El mínimo no puede ser menor a :min.',
            'maximo.integer' => 'El máximo debe ser un número entero.',
            'maximo.min' => 'El máximo no puede ser menor a :min.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra.min' => 'El precio de compra no puede ser menor a :min.',
            'precio_compra.regex' => 'El precio de compra debe tener máximo 2 decimales.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta no puede ser menor a :min.',
            'precio_venta.regex' => 'El precio de venta debe tener máximo 2 decimales.',
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
