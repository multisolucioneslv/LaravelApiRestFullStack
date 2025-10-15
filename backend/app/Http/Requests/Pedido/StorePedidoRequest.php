<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
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
            'fecha' => 'required|date',
            'fecha_entrega' => 'nullable|date|after_or_equal:fecha',
            'tipo' => 'required|string|in:compra,venta,servicio',
            'estado' => 'required|string|in:pendiente,proceso,completado,cancelado',
            'empresa_id' => 'required|exists:empresas,id',
            'total' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
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
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha debe ser una fecha válida',
            'fecha_entrega.date' => 'La fecha de entrega debe ser una fecha válida',
            'fecha_entrega.after_or_equal' => 'La fecha de entrega debe ser igual o posterior a la fecha del pedido',
            'tipo.required' => 'El tipo de pedido es obligatorio',
            'tipo.in' => 'El tipo debe ser: compra, venta o servicio',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado debe ser: pendiente, proceso, completado o cancelado',
            'empresa_id.required' => 'La empresa es obligatoria',
            'empresa_id.exists' => 'La empresa seleccionada no existe',
            'total.required' => 'El total es obligatorio',
            'total.numeric' => 'El total debe ser un número',
            'total.min' => 'El total debe ser mayor o igual a 0',
            'observaciones.string' => 'Las observaciones deben ser texto',
        ];
    }
}
