<?php

namespace Database\Seeders;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PedidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener usuarios
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('No hay usuarios disponibles.');
            return;
        }

        $count = 0;
        $tipos = ['compra', 'venta', 'transferencia'];
        $estados = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];

        // Crear 10-20 pedidos
        for ($i = 0; $i < 15; $i++) {
            $user = $faker->randomElement($users);
            $tipo = $faker->randomElement($tipos);

            $fecha = $faker->dateTimeBetween('-1 month', 'now');

            // Estados progresivos basados en la fecha
            $estadoSeleccionado = $faker->randomElement($estados);

            // Si el pedido es antiguo, tiene más probabilidad de estar entregado
            $diasDesdeCreacion = $fecha->diff(new \DateTime())->days;
            if ($diasDesdeCreacion > 15) {
                $estadoSeleccionado = $faker->randomElement(['entregado', 'procesando', 'cancelado']);
            } elseif ($diasDesdeCreacion > 7) {
                $estadoSeleccionado = $faker->randomElement(['enviado', 'procesando', 'entregado']);
            } else {
                $estadoSeleccionado = $faker->randomElement(['pendiente', 'procesando']);
            }

            // Fecha de entrega (solo si está procesando, enviado o entregado)
            $fechaEntrega = null;
            if (in_array($estadoSeleccionado, ['procesando', 'enviado', 'entregado'])) {
                $diasEntrega = $faker->numberBetween(3, 15);
                $fechaEntrega = (clone $fecha)->modify("+{$diasEntrega} days");
            }

            Pedido::create([
                'codigo' => 'PED-' . strtoupper($faker->bothify('####??')),
                'fecha' => $fecha->format('Y-m-d'),
                'fecha_entrega' => $fechaEntrega ? $fechaEntrega->format('Y-m-d') : null,
                'tipo' => $tipo,
                'estado' => $estadoSeleccionado,
                'observaciones' => $faker->boolean(70) ? $faker->sentence(12) : null,
                'empresa_id' => $user->empresa_id,
                'user_id' => $user->id,
                'total' => $faker->randomFloat(2, 100, 5000),
            ]);

            $count++;
        }

        $this->command->info("{$count} pedidos creados exitosamente con estados progresivos");
    }
}
