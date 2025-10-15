<?php

namespace App\Http\Requests\Sex;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreSexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sexo' => 'required|string|max:50',
            'inicial' => 'required|string|size:1',
        ];
    }

    public function messages(): array
    {
        return [
            'sexo.required' => 'El campo sexo es requerido.',
            'sexo.max' => 'El sexo no puede exceder los :max caracteres.',
            'inicial.required' => 'El campo inicial es requerido.',
            'inicial.size' => 'La inicial debe ser exactamente 1 carácter.',
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
