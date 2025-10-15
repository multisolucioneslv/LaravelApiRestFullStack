<?php

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'porcentaje' => 'required|numeric|min:0|max:100',
            'descripcion' => 'nullable|string',
            'empresa_id' => 'required|integer|exists:empresas,id',
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.max' => 'El nombre no puede exceder los :max caracteres.',
            'porcentaje.required' => 'El campo porcentaje es requerido.',
            'porcentaje.numeric' => 'El porcentaje debe ser un número.',
            'porcentaje.min' => 'El porcentaje no puede ser menor a :min.',
            'porcentaje.max' => 'El porcentaje no puede ser mayor a :max.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'empresa_id.required' => 'El campo empresa es requerido.',
            'empresa_id.integer' => 'El ID de empresa debe ser un número entero.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
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
