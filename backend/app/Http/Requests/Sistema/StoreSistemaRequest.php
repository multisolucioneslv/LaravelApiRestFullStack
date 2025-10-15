<?php

namespace App\Http\Requests\Sistema;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreSistemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'version' => 'nullable|string|max:20',
            'configuracion' => 'nullable|array',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:5120', // Max 5MB
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.max' => 'El nombre no puede exceder los :max caracteres.',
            'version.max' => 'La versión no puede exceder los :max caracteres.',
            'configuracion.array' => 'La configuración debe ser un objeto JSON válido.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'La imagen debe ser de tipo: jpeg, jpg, png, gif, webp o svg.',
            'logo.max' => 'La imagen no puede exceder los 5MB.',
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
