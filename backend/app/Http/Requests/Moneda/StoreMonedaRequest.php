<?php

namespace App\Http\Requests\Moneda;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreMonedaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:10|unique:currencies,codigo',
            'nombre' => 'required|string|max:100',
            'simbolo' => 'required|string|max:10',
            'tasa_cambio' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,4})?$/',
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El campo código es requerido.',
            'codigo.unique' => 'Este código ya está registrado.',
            'codigo.max' => 'El código no puede exceder los :max caracteres.',
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.max' => 'El nombre no puede exceder los :max caracteres.',
            'simbolo.required' => 'El campo símbolo es requerido.',
            'simbolo.max' => 'El símbolo no puede exceder los :max caracteres.',
            'tasa_cambio.required' => 'El campo tasa de cambio es requerido.',
            'tasa_cambio.numeric' => 'La tasa de cambio debe ser un número.',
            'tasa_cambio.min' => 'La tasa de cambio debe ser mayor o igual a :min.',
            'tasa_cambio.regex' => 'La tasa de cambio puede tener máximo 4 decimales.',
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
