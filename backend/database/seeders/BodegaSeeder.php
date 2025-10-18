<?php

namespace Database\Seeders;

use App\Models\Bodega;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BodegaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener todas las empresas
        $empresas = Empresa::all();

        if ($empresas->isEmpty()) {
            $this->command->error('No hay empresas disponibles. Ejecuta primero EmpresaSeeder.');
            return;
        }

        $tiposBodegas = [
            'Bodega Principal',
            'Almacén Norte',
            'Almacén Sur',
            'Depósito Central',
            'Bodega Zona Este',
            'Bodega Zona Oeste',
            'Centro de Distribución',
            'Almacén Temporal',
        ];

        $count = 0;

        foreach ($empresas as $empresa) {
            // Crear 5-8 bodegas por empresa
            $numBodegas = $faker->numberBetween(5, 8);

            for ($i = 0; $i < $numBodegas; $i++) {
                $nombreBodega = $faker->randomElement($tiposBodegas);

                // Si ya existe ese nombre, agregar número
                $nombreFinal = $nombreBodega;
                $numero = 1;
                while (Bodega::where('empresa_id', $empresa->id)->where('nombre', $nombreFinal)->exists()) {
                    $nombreFinal = $nombreBodega . ' ' . $numero++;
                }

                Bodega::create([
                    'nombre' => $nombreFinal,
                    'codigo' => strtoupper($faker->bothify('BOD-###??')),
                    'direccion' => $faker->address(),
                    'empresa_id' => $empresa->id,
                    'activo' => $faker->boolean(90), // 90% activas
                ]);

                $count++;
            }
        }

        $this->command->info("{$count} bodegas creadas exitosamente");
    }
}
