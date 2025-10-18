<?php

namespace Database\Seeders;

use App\Models\Bodega;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class InventarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener todos los productos
        $productos = Producto::with('empresa')->get();

        if ($productos->isEmpty()) {
            $this->command->error('No hay productos disponibles. Ejecuta primero ProductoSeeder.');
            return;
        }

        $count = 0;

        foreach ($productos as $producto) {
            // Obtener bodegas de la misma empresa del producto
            $bodegas = Bodega::where('empresa_id', $producto->empresa_id)
                ->where('activo', true)
                ->get();

            if ($bodegas->isEmpty()) {
                continue;
            }

            // Cada producto estará en 1-3 bodegas diferentes
            $numBodegas = $faker->numberBetween(1, min(3, $bodegas->count()));
            $bodegasSeleccionadas = $faker->randomElements($bodegas->toArray(), $numBodegas);

            foreach ($bodegasSeleccionadas as $bodega) {
                $cantidad = $faker->numberBetween(10, 200);

                Inventario::create([
                    'nombre' => $producto->nombre,
                    'codigo' => $producto->sku . '-' . $bodega['codigo'],
                    'descripcion' => $producto->descripcion,
                    'producto_id' => $producto->id,
                    'galeria_id' => null, // Se asignará con GaleriaSeeder
                    'bodega_id' => $bodega['id'],
                    'empresa_id' => $producto->empresa_id,
                    'cantidad' => $cantidad,
                    'minimo' => $faker->numberBetween(5, 15),
                    'maximo' => $faker->numberBetween(100, 500),
                    'precio_compra' => $producto->precio_compra,
                    'precio_venta' => $producto->precio_venta,
                    'activo' => true,
                ]);

                $count++;
            }
        }

        $this->command->info("{$count} registros de inventario creados exitosamente");
    }
}
