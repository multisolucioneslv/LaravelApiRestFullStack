<?php

namespace App\Http\Requests\Ruta;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreRutaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sistema_id' => 'required|integer|exists:sistemas,id',
            'ruta' => 'required|string|max:255',
            'metodo' => 'required|string|in:GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD',
            'descripcion' => 'nullable|string|max:500',
            'controlador' => 'required|string|max:255',
            'accion' => 'required|string|max:100',
            'middleware' => 'nullable|array',
            'middleware.*' => 'string|max:100',
            'activo' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'sistema_id.required' => 'El campo sistema es requerido.',
            'sistema_id.integer' => 'El sistema debe ser un número válido.',
            'sistema_id.exists' => 'El sistema seleccionado no existe.',
            'ruta.required' => 'El campo ruta es requerido.',
            'ruta.max' => 'La ruta no puede exceder los :max caracteres.',
            'metodo.required' => 'El campo método HTTP es requerido.',
            'metodo.in' => 'El método HTTP debe ser GET, POST, PUT, PATCH, DELETE, OPTIONS o HEAD.',
            'descripcion.max' => 'La descripción no puede exceder los :max caracteres.',
            'controlador.required' => 'El campo controlador es requerido.',
            'controlador.max' => 'El controlador no puede exceder los :max caracteres.',
            'accion.required' => 'El campo acción es requerido.',
            'accion.max' => 'La acción no puede exceder los :max caracteres.',
            'middleware.array' => 'El campo middleware debe ser un arreglo.',
            'middleware.*.string' => 'Cada middleware debe ser una cadena de texto.',
            'middleware.*.max' => 'Cada middleware no puede exceder los :max caracteres.',
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
