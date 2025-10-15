<?php

namespace App\Http\Requests\Galeria;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UpdateGaleriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'galeriable_type' => 'nullable|string',
            'galeriable_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.max' => 'El nombre no puede exceder los :max caracteres.',
            'imagenes.array' => 'Las imágenes deben ser un array.',
            'imagenes.*.image' => 'Cada archivo debe ser una imagen válida.',
            'imagenes.*.mimes' => 'Las imágenes deben ser de tipo: jpeg, jpg, png, gif o webp.',
            'imagenes.*.max' => 'Cada imagen no puede exceder los 5MB.',
            'galeriable_type.string' => 'El tipo de relación debe ser una cadena de texto.',
            'galeriable_id.integer' => 'El ID de relación debe ser un número entero.',
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
