<?php

namespace App\Http\Requests\Empresa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'telefono_id' => 'nullable|exists:telefonos,id',
            'moneda_id' => 'nullable|exists:monedas,id',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:5120', // Max 5MB
            'favicon' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg,ico|max:2048', // Max 2MB
            'fondo_login' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240', // Max 10MB
            'zona_horaria' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.max' => 'El nombre no puede exceder los :max caracteres.',
            'telefono_id.exists' => 'El teléfono seleccionado no es válido.',
            'moneda_id.exists' => 'La moneda seleccionada no es válida.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.max' => 'El email no puede exceder los :max caracteres.',
            'direccion.string' => 'La dirección debe ser texto válido.',
            'logo.image' => 'El logo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser de tipo: jpeg, jpg, png, gif, webp o svg.',
            'logo.max' => 'El logo no puede exceder los 5MB.',
            'favicon.image' => 'El favicon debe ser una imagen.',
            'favicon.mimes' => 'El favicon debe ser de tipo: jpeg, jpg, png, gif, webp, svg o ico.',
            'favicon.max' => 'El favicon no puede exceder los 2MB.',
            'fondo_login.image' => 'El fondo de login debe ser una imagen.',
            'fondo_login.mimes' => 'El fondo de login debe ser de tipo: jpeg, jpg, png, gif o webp.',
            'fondo_login.max' => 'El fondo de login no puede exceder los 10MB.',
            'zona_horaria.max' => 'La zona horaria no puede exceder los :max caracteres.',
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
