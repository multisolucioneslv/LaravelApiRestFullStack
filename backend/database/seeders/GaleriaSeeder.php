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

            // Colores variados para las imágenes
            $colores = ['0066CC', 'FF6B6B', '4ECDC4', 'FFE66D', '95E1D3', 'FF8B94', '6C5CE7', 'FD79A8', '00B894', 'FDCB6E'];

            for ($i = 0; $i < $numImagenes; $i++) {
                $colorAleatorio = $faker->randomElement($colores);
                $nombreImagen = urlencode(substr($producto->nombre, 0, 15));
                $angulo = ['Front', 'Back', 'Side', 'Detail', 'Close'][$i] ?? 'View' . $i;

                $imagenes[] = [
                    'url' => "https://via.placeholder.com/400x400/{$colorAleatorio}/FFFFFF?text={$nombreImagen}+{$angulo}",
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
