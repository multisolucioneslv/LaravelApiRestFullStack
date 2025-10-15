<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario' => 'required|string|max:100|unique:users,usuario',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // Max 5MB
            'sexo_id' => 'nullable|exists:sexes,id',
            'telefono' => 'nullable|string|max:20',
            'chatid' => 'nullable|string|max:100',
            'empresa_id' => 'nullable|exists:empresas,id',
            'activo' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'usuario.required' => 'El campo usuario es requerido.',
            'usuario.unique' => 'Este usuario ya está registrado.',
            'usuario.max' => 'El usuario no puede exceder los :max caracteres.',
            'name.required' => 'El campo nombre es requerido.',
            'name.max' => 'El nombre no puede exceder los :max caracteres.',
            'email.required' => 'El campo email es requerido.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Este email ya está registrado.',
            'email.max' => 'El email no puede exceder los :max caracteres.',
            'password.required' => 'El campo contraseña es requerido.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.mimes' => 'La imagen debe ser de tipo: jpeg, jpg, png, gif o webp.',
            'avatar.max' => 'La imagen no puede exceder los 5MB.',
            'sexo_id.exists' => 'El sexo seleccionado no es válido.',
            'telefono.max' => 'El teléfono no puede exceder los :max caracteres.',
            'chatid.max' => 'El chat ID no puede exceder los :max caracteres.',
            'empresa_id.exists' => 'La empresa seleccionada no es válida.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
            'roles.array' => 'Los roles deben ser un array.',
            'roles.*.exists' => 'Uno o más roles seleccionados no son válidos.',
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
