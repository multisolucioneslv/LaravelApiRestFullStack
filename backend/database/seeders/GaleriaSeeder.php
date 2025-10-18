<?php

namespace Database\Seeders;

use App\Models\Galeria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class GaleriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener todos los productos
        $productos = Producto::all();

        if ($productos->isEmpty()) {
            $this->command->error('No hay productos disponibles. Ejecuta primero ProductoSeeder.');
            return;
        }

        $count = 0;

        foreach ($productos as $producto) {
            // Crear una galería para cada producto con 2-5 imágenes
            $numImagenes = $faker->numberBetween(2, 5);
            $imagenes = [];

            for ($i = 0; $i < $numImagenes; $i++) {
                // Usar picsum.photos con seed basado en producto ID + índice de imagen
                $seed = ($producto->id * 100) + $i;

                $imagenes[] = [
                    'url' => "https://picsum.photos/400/400?random={$seed}",
                    'orden' => $i + 1,
                    'es_principal' => $i === 0, // La primera es la principal
                ];
            }

            Galeria::create([
                'nombre' => "Galería {$producto->nombre}",
                'imagenes' => json_encode($imagenes),
                'galeriable_id' => $producto->id,
                'galeriable_type' => Producto::class,
            ]);

            $count++;
        }

        $this->command->info("{$count} galerías creadas exitosamente");
    }
}
