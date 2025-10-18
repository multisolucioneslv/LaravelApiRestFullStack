<?php

namespace Database\Seeders;

use App\Models\Cotizacion;
use App\Models\Currency;
use App\Models\DetalleCotizacion;
use App\Models\Inventario;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CotizacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener usuarios, inventarios, monedas e impuestos
        $users = User::all();
        $inventarios = Inventario::where('activo', true)->get();
        $currencies = Currency::all();
        $taxes = Tax::all();

        if ($users->isEmpty() || $inventarios->isEmpty()) {
            $this->command->error('No hay usuarios o inventarios disponibles.');
            return;
        }

        $count = 0;
        $estados = ['pendiente', 'aprobada', 'rechazada', 'vencida'];

        // Crear 20-30 cotizaciones
        for ($i = 0; $i < 25; $i++) {
            $user = $faker->randomElement($users);
            $currency = $faker->randomElement($currencies);
            $tax = $faker->randomElement($taxes);

            $fecha = $faker->dateTimeBetween('-3 months', 'now');
            $fechaVencimiento = (clone $fecha)->modify('+' . $faker->numberBetween(7, 30) . ' days');

            $cotizacion = Cotizacion::create([
                'codigo' => 'COT-' . strtoupper($faker->bothify('####??')),
                'fecha' => $fecha->format('Y-m-d'),
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'estado' => $faker->randomElement($estados),
                'observaciones' => $faker->boolean(70) ? $faker->sentence(10) : null,
                'empresa_id' => $user->empresa_id,
                'user_id' => $user->id,
                'currency_id' => $currency->id,
                'tax_id' => $tax->id,
                'subtotal' => 0,
                'impuesto' => 0,
                'descuento' => 0,
                'total' => 0,
            ]);

            // Agregar 2-10 productos a cada cotización
            $numProductos = $faker->numberBetween(2, 10);
            $inventariosAleatorios = $faker->randomElements($inventarios->toArray(), $numProductos);

            $subtotal = 0;

            foreach ($inventariosAleatorios as $inv) {
                $cantidad = $faker->numberBetween(1, 10);
                $precioUnitario = $inv['precio_venta'];
                $descuento = $faker->boolean(30) ? $faker->randomFloat(2, 0, $precioUnitario * 0.15) : 0;
                $subtotalDetalle = ($precioUnitario * $cantidad) - $descuento;

                DetalleCotizacion::create([
                    'cotizacion_id' => $cotizacion->id,
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
            $descuentoGeneral = $faker->boolean(20) ? $faker->randomFloat(2, 0, $subtotal * 0.05) : 0;
            $total = $subtotal + $impuesto - $descuentoGeneral;

            // Actualizar cotización con totales
            $cotizacion->update([
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'descuento' => $descuentoGeneral,
                'total' => $total,
            ]);

            $count++;
        }

        $this->command->info("{$count} cotizaciones creadas exitosamente con sus detalles");
    }
}
