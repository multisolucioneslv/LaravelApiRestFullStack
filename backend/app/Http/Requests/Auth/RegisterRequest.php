<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'usuario' => 'required|string|max:100|unique:users,usuario',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'sexo_id' => 'nullable|exists:sexes,id',
            'telefono_id' => 'nullable|exists:telefonos,id',
            'chatid_id' => 'nullable|exists:chatids,id',
            'empresa_id' => 'nullable|exists:empresas,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'usuario.required' => 'El campo usuario es requerido.',
            'usuario.unique' => 'Este usuario ya está registrado.',
            'name.required' => 'El campo nombre es requerido.',
            'email.required' => 'El campo email es requerido.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'El campo contraseña es requerido.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'sexo_id.exists' => 'El sexo seleccionado no es válido.',
            'telefono_id.exists' => 'El teléfono seleccionado no es válido.',
            'chatid_id.exists' => 'El chat ID seleccionado no es válido.',
            'empresa_id.exists' => 'La empresa seleccionada no es válida.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password' => 'contraseña',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
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
