<?php

namespace App\Http\Requests\Empresa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El usuario debe estar autenticado y tener una empresa asignada
        return auth()->check() && auth()->user()->empresa_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'zona_horaria' => 'required|string|max:50',
            'moneda_id' => 'nullable|exists:monedas,id',
            'horarios' => 'nullable|array',
            'horarios.*.dia' => 'required_with:horarios|string|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'horarios.*.abierto' => 'required_with:horarios|boolean',
            'horarios.*.apertura' => 'nullable|required_if:horarios.*.abierto,true|date_format:H:i',
            'horarios.*.cierre' => 'nullable|required_if:horarios.*.abierto,true|date_format:H:i',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,ico|max:1024',
            'fondo_login' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
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
            // Nombre
            'nombre.required' => 'El nombre de la empresa es obligatorio',
            'nombre.string' => 'El nombre debe ser una cadena de texto',
            'nombre.max' => 'El nombre no puede exceder los 200 caracteres',

            // Email
            'email.email' => 'El email debe ser una dirección de correo válida',
            'email.max' => 'El email no puede exceder los 100 caracteres',

            // Dirección
            'direccion.string' => 'La dirección debe ser una cadena de texto',

            // Zona horaria
            'zona_horaria.required' => 'La zona horaria es obligatoria',
            'zona_horaria.string' => 'La zona horaria debe ser una cadena de texto',
            'zona_horaria.max' => 'La zona horaria no puede exceder los 50 caracteres',

            // Moneda
            'moneda_id.exists' => 'La moneda seleccionada no existe',

            // Horarios
            'horarios.array' => 'Los horarios deben ser un arreglo',
            'horarios.*.dia.required_with' => 'El día es obligatorio cuando se especifican horarios',
            'horarios.*.dia.string' => 'El día debe ser una cadena de texto',
            'horarios.*.dia.in' => 'El día debe ser uno de los días de la semana válidos',
            'horarios.*.abierto.required_with' => 'El estado de abierto es obligatorio',
            'horarios.*.abierto.boolean' => 'El estado de abierto debe ser verdadero o falso',
            'horarios.*.apertura.required_if' => 'La hora de apertura es obligatoria cuando el negocio está abierto',
            'horarios.*.apertura.date_format' => 'La hora de apertura debe tener el formato HH:MM',
            'horarios.*.cierre.required_if' => 'La hora de cierre es obligatoria cuando el negocio está abierto',
            'horarios.*.cierre.date_format' => 'La hora de cierre debe tener el formato HH:MM',

            // Logo
            'logo.image' => 'El logo debe ser una imagen',
            'logo.mimes' => 'El logo debe ser un archivo de tipo: jpeg, jpg, png, gif, webp',
            'logo.max' => 'El logo no puede ser mayor a 2MB',

            // Favicon
            'favicon.image' => 'El favicon debe ser una imagen',
            'favicon.mimes' => 'El favicon debe ser un archivo de tipo: jpeg, jpg, png, gif, webp, ico',
            'favicon.max' => 'El favicon no puede ser mayor a 1MB',

            // Fondo de login
            'fondo_login.image' => 'El fondo de login debe ser una imagen',
            'fondo_login.mimes' => 'El fondo de login debe ser un archivo de tipo: jpeg, jpg, png, gif, webp',
            'fondo_login.max' => 'El fondo de login no puede ser mayor a 5MB',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre de la empresa',
            'email' => 'correo electrónico',
            'direccion' => 'dirección',
            'zona_horaria' => 'zona horaria',
            'moneda_id' => 'moneda',
            'horarios' => 'horarios de atención',
            'horarios.*.dia' => 'día',
            'horarios.*.abierto' => 'estado de abierto',
            'horarios.*.apertura' => 'hora de apertura',
            'horarios.*.cierre' => 'hora de cierre',
            'logo' => 'logo',
            'favicon' => 'favicon',
            'fondo_login' => 'fondo de login',
        ];
    }
}
