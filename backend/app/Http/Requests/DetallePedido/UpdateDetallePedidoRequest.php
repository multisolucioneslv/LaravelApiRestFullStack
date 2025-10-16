<?php

namespace App\Http\Requests\DetallePedido;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDetallePedidoRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para actualizar un detalle de pedido.
     */
    public function rules(): array
    {
        return [
            'pedido_id' => 'required|integer|exists:pedidos,id',
            'inventario_id' => 'required|integer|exists:inventarios,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
        ];
    }

    /**
     * Mensajes de validación personalizados en español.
     */
    public function messages(): array
    {
        return [
            'pedido_id.required' => 'El pedido es requerido',
            'pedido_id.integer' => 'El ID del pedido debe ser un número entero',
            'pedido_id.exists' => 'El pedido seleccionado no existe',

            'inventario_id.required' => 'El producto/inventario es requerido',
            'inventario_id.integer' => 'El ID del inventario debe ser un número entero',
            'inventario_id.exists' => 'El producto/inventario seleccionado no existe',

            'cantidad.required' => 'La cantidad es requerida',
            'cantidad.integer' => 'La cantidad debe ser un número entero',
            'cantidad.min' => 'La cantidad debe ser al menos 1',

            'precio_unitario.required' => 'El precio unitario es requerido',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número',
            'precio_unitario.min' => 'El precio unitario no puede ser negativo',

            'descuento.numeric' => 'El descuento debe ser un número',
            'descuento.min' => 'El descuento no puede ser negativo',

            'subtotal.required' => 'El subtotal es requerido',
            'subtotal.numeric' => 'El subtotal debe ser un número',
            'subtotal.min' => 'El subtotal no puede ser negativo',
        ];
    }

    /**
     * Nombres personalizados de los atributos en español.
     */
    public function attributes(): array
    {
        return [
            'pedido_id' => 'pedido',
            'inventario_id' => 'producto',
            'cantidad' => 'cantidad',
            'precio_unitario' => 'precio unitario',
            'descuento' => 'descuento',
            'subtotal' => 'subtotal',
        ];
    }
}
