<?php

namespace App\Http\Requests\Cotizacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha',
            'estado' => 'nullable|in:pendiente,aprobada,rechazada,convertida',
            'observaciones' => 'nullable|string',
            'empresa_id' => 'required|exists:empresas,id',
            'moneda_id' => 'nullable|exists:monedas,id',
            'tax_id' => 'nullable|exists:taxes,id',

            // Validaciones para detalles (array)
            'detalles' => 'required|array|min:1',
            'detalles.*.inventario_id' => 'required|exists:inventarios,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'detalles.*.descuento' => 'nullable|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'El campo fecha es requerido.',
            'fecha.date' => 'La fecha debe ser una fecha válida.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a la fecha de cotización.',
            'estado.in' => 'El estado debe ser: pendiente, aprobada, rechazada o convertida.',
            'observaciones.string' => 'Las observaciones deben ser texto.',
            'empresa_id.required' => 'El campo empresa es requerido.',
            'empresa_id.exists' => 'La empresa seleccionada no es válida.',
            'moneda_id.exists' => 'La moneda seleccionada no es válida.',
            'tax_id.exists' => 'El impuesto seleccionado no es válido.',

            // Mensajes para detalles
            'detalles.required' => 'Debe agregar al menos un detalle a la cotización.',
            'detalles.array' => 'Los detalles deben ser un array.',
            'detalles.min' => 'Debe agregar al menos un producto a la cotización.',
            'detalles.*.inventario_id.required' => 'El producto es requerido en cada detalle.',
            'detalles.*.inventario_id.exists' => 'El producto seleccionado no es válido.',
            'detalles.*.cantidad.required' => 'La cantidad es requerida en cada detalle.',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'detalles.*.precio_unitario.required' => 'El precio unitario es requerido en cada detalle.',
            'detalles.*.precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'detalles.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
            'detalles.*.precio_unitario.regex' => 'El precio unitario debe tener máximo 2 decimales.',
            'detalles.*.descuento.numeric' => 'El descuento debe ser un número.',
            'detalles.*.descuento.min' => 'El descuento no puede ser negativo.',
            'detalles.*.descuento.regex' => 'El descuento debe tener máximo 2 decimales.',
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
