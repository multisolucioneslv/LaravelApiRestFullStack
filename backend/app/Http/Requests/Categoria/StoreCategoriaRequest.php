<?php

namespace App\Http\Requests\Categoria;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'slug' => 'nullable|string|max:200|unique:categorias,slug',
            'icono' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20|regex:/^#[a-fA-F0-9]{6}$/',
            'activo' => 'nullable|boolean',
            'empresa_id' => 'required|exists:empresas,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es requerido.',
            'nombre.max' => 'El nombre no puede exceder los :max caracteres.',
            'slug.max' => 'El slug no puede exceder los :max caracteres.',
            'slug.unique' => 'Este slug ya está registrado.',
            'icono.max' => 'El icono no puede exceder los :max caracteres.',
            'color.max' => 'El color no puede exceder los :max caracteres.',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF5733).',
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
