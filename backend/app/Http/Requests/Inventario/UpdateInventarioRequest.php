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
            'codigo.required' => 'El campo c�digo es requerido.',
            'codigo.max' => 'El c�digo no puede exceder los :max caracteres.',
            'codigo.unique' => 'Este c�digo ya est� registrado.',
            'descripcion.string' => 'La descripci�n debe ser texto.',
            'galeria_id.exists' => 'La galer�a seleccionada no es v�lida.',
            'bodega_id.required' => 'El campo bodega es requerido.',
            'bodega_id.exists' => 'La bodega seleccionada no es v�lida.',
            'empresa_id.required' => 'El campo empresa es requerido.',
            'empresa_id.exists' => 'La empresa seleccionada no es v�lida.',
            'cantidad.required' => 'El campo cantidad es requerido.',
            'cantidad.integer' => 'La cantidad debe ser un n�mero entero.',
            'cantidad.min' => 'La cantidad no puede ser menor a :min.',
            'minimo.required' => 'El campo m�nimo es requerido.',
            'minimo.integer' => 'El m�nimo debe ser un n�mero entero.',
            'minimo.min' => 'El m�nimo no puede ser menor a :min.',
            'maximo.required' => 'El campo m�ximo es requerido.',
            'maximo.integer' => 'El m�ximo debe ser un n�mero entero.',
            'maximo.min' => 'El m�ximo no puede ser menor a :min.',
            'precio_compra.required' => 'El campo precio de compra es requerido.',
            'precio_compra.numeric' => 'El precio de compra debe ser un n�mero.',
            'precio_compra.min' => 'El precio de compra no puede ser menor a :min.',
            'precio_compra.regex' => 'El precio de compra debe tener m�ximo 2 decimales.',
            'precio_venta.required' => 'El campo precio de venta es requerido.',
            'precio_venta.numeric' => 'El precio de venta debe ser un n�mero.',
            'precio_venta.min' => 'El precio de venta no puede ser menor a :min.',
            'precio_venta.regex' => 'El precio de venta debe tener m�ximo 2 decimales.',
            'activo.required' => 'El campo activo es requerido.',
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
