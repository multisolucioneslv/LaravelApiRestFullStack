<?php

namespace Database\Seeders;

use App\Models\Cotizacion;
use App\Models\Currency;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Tax;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener usuarios, inventarios, cotizaciones, monedas e impuestos
        $users = User::all();
        $inventarios = Inventario::where('activo', true)->get();
        $cotizaciones = Cotizacion::where('estado', 'aprobada')->get();
        $currencies = Currency::all();
        $taxes = Tax::all();

        if ($users->isEmpty() || $inventarios->isEmpty()) {
            $this->command->error('No hay usuarios o inventarios disponibles.');
            return;
        }

        $count = 0;
        $estados = ['completada', 'pendiente', 'cancelada'];
        $tiposPago = ['efectivo', 'tarjeta', 'transferencia', 'cheque', 'paypal'];

        // Crear 15-25 ventas
        for ($i = 0; $i < 20; $i++) {
            $user = $faker->randomElement($users);
            $currency = $faker->randomElement($currencies);
            $tax = $faker->randomElement($taxes);

            // Algunas ventas vienen de cotizaciones, otras directas
            $cotizacion = null;
            if ($faker->boolean(40) && $cotizaciones->isNotEmpty()) {
                $cotizacion = $faker->randomElement($cotizaciones);
            }

            $fecha = $faker->dateTimeBetween('-2 months', 'now');

            $venta = Venta::create([
                'codigo' => 'VEN-' . strtoupper($faker->bothify('####??')),
                'fecha' => $fecha->format('Y-m-d'),
                'estado' => $faker->randomElement($estados),
                'tipo_pago' => $faker->randomElement($tiposPago),
                'observaciones' => $faker->boolean(60) ? $faker->sentence(8) : null,
                'empresa_id' => $user->empresa_id,
                'user_id' => $user->id,
                'cotizacion_id' => $cotizacion ? $cotizacion->id : null,
                'currency_id' => $currency->id,
                'tax_id' => $tax->id,
                'subtotal' => 0,
                'impuesto' => 0,
                'descuento' => 0,
                'total' => 0,
            ]);

            // Agregar 1-8 productos a cada venta
            $numProductos = $faker->numberBetween(1, 8);
            $inventariosAleatorios = $faker->randomElements($inventarios->toArray(), $numProductos);

            $subtotal = 0;

            foreach ($inventariosAleatorios as $inv) {
                $cantidad = $faker->numberBetween(1, 5);
                $precioUnitario = $inv['precio_venta'];
                $descuento = $faker->boolean(25) ? $faker->randomFloat(2, 0, $precioUnitario * 0.10) : 0;
                $subtotalDetalle = ($precioUnitario * $cantidad) - $descuento;

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'inventario_id' => $inv['id'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuento,
                    'subtotal' => $subtotalDetalle,
                ]);

                $subtotal += $subtotalDetalle;
            }

            // Calcular impuestos y total
            $impuesto = $subtotal * ($tax->porcentaje / 100);
            $descuentoGeneral = $faker->boolean(15) ? $faker->randomFloat(2, 0, $subtotal * 0.03) : 0;
            $total = $subtotal + $impuesto - $descuentoGeneral;

            // Actualizar venta con totales
            $venta->update([
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'descuento' => $descuentoGeneral,
                'total' => $total,
            ]);

            $count++;
        }

        $this->command->info("{$count} ventas creadas exitosamente con sus detalles");
    }
}
