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
            'codigo.required' => 'El campo c�digo es requerido.',
            'codigo.max' => 'El c�digo no puede exceder los :max caracteres.',
            'codigo.unique' => 'Este c�digo ya est� registrado.',
            'descripcion.string' => 'La descripci�n debe ser texto.',
            'galeria_id.exists' => 'La galer�a seleccionada no es v�lida.',
            'bodega_id.required' => 'El campo bodega es requerido.',
            'bodega_id.exists' => 'La bodega seleccionada no es v�lida.',
            'empresa_id.required' => 'El campo empresa es requerido.',
            'empresa_id.exists' => 'La empresa seleccionada no es v�lida.',
            'cantidad.integer' => 'La cantidad debe ser un n�mero entero.',
            'cantidad.min' => 'La cantidad no puede ser menor a :min.',
            'minimo.integer' => 'El m�nimo debe ser un n�mero entero.',
            'minimo.min' => 'El m�nimo no puede ser menor a :min.',
            'maximo.integer' => 'El m�ximo debe ser un n�mero entero.',
            'maximo.min' => 'El m�ximo no puede ser menor a :min.',
            'precio_compra.numeric' => 'El precio de compra debe ser un n�mero.',
            'precio_compra.min' => 'El precio de compra no puede ser menor a :min.',
            'precio_compra.regex' => 'El precio de compra debe tener m�ximo 2 decimales.',
            'precio_venta.numeric' => 'El precio de venta debe ser un n�mero.',
            'precio_venta.min' => 'El precio de venta no puede ser menor a :min.',
            'precio_venta.regex' => 'El precio de venta debe tener m�ximo 2 decimales.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validaci�n',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
