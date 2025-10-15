<?php

namespace App\Http\Requests\Bodega;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreBodegaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'codigo' => 'required|string|max:50|unique:bodegas,codigo',
            'direccion' => 'nullable|string',
            'empresa_id' => 'required|exists:empresas,id',
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
            'direccion.string' => 'La dirección debe ser texto válido.',
            'empresa_id.required' => 'El campo empresa es requerido.',
            'empresa_id.exists' => 'La empresa seleccionada no es válida.',
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
