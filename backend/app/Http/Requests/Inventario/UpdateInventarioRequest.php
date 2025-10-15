<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UpdateInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $inventarioId = $this->route('id');

        return [
            'nombre' => 'required|string|max:200',
            'codigo' => [
                'required',
                'string',
                'max:100',
                Rule::unique('inventarios', 'codigo')->ignore($inventarioId),
            ],
            'descripcion' => 'nullable|string',
            'galeria_id' => 'nullable|exists:galerias,id',
            'bodega_id' => 'required|exists:bodegas,id',
            'empresa_id' => 'required|exists:empresas,id',
            'cantidad' => 'required|integer|min:0',
            'minimo' => 'required|integer|min:0',
            'maximo' => 'required|integer|min:0',
            'precio_compra' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'precio_venta' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'activo' => 'required|boolean',
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
            'cantidad.required' => 'El campo cantidad es requerido.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad no puede ser menor a :min.',
            'minimo.required' => 'El campo mínimo es requerido.',
            'minimo.integer' => 'El mínimo debe ser un número entero.',
            'minimo.min' => 'El mínimo no puede ser menor a :min.',
            'maximo.required' => 'El campo máximo es requerido.',
            'maximo.integer' => 'El máximo debe ser un número entero.',
            'maximo.min' => 'El máximo no puede ser menor a :min.',
            'precio_compra.required' => 'El campo precio de compra es requerido.',
            'precio_compra.numeric' => 'El precio de compra debe ser un número.',
            'precio_compra.min' => 'El precio de compra no puede ser menor a :min.',
            'precio_compra.regex' => 'El precio de compra debe tener máximo 2 decimales.',
            'precio_venta.required' => 'El campo precio de venta es requerido.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta no puede ser menor a :min.',
            'precio_venta.regex' => 'El precio de venta debe tener máximo 2 decimales.',
            'activo.required' => 'El campo activo es requerido.',
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
