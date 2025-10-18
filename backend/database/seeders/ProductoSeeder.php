<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Obtener todas las categorías de la empresa 1
        $categorias = Categoria::where('empresa_id', 1)->pluck('id')->toArray();

        if (empty($categorias)) {
            $this->command->error('No hay categorías disponibles. Ejecuta primero CategoriaSeeder.');
            return;
        }

        $unidadesMedida = ['Unidad', 'Kg', 'Litro', 'Metro', 'Caja', 'Paquete', 'Pieza'];

        // Productos de ejemplo por categoría (con imágenes)
        $productosBase = [
            // Electrónica
            'Laptop HP 15"' => ['categoria' => 'Electrónica', 'precio_compra' => 450, 'precio_venta' => 650, 'imagen' => 'https://via.placeholder.com/400x400/0066CC/FFFFFF?text=Laptop'],
            'Mouse Inalámbrico' => ['categoria' => 'Electrónica', 'precio_compra' => 8, 'precio_venta' => 15],
            'Teclado Mecánico' => ['categoria' => 'Electrónica', 'precio_compra' => 35, 'precio_venta' => 55],
            'Monitor 24" LED' => ['categoria' => 'Electrónica', 'precio_compra' => 120, 'precio_venta' => 180],
            'Auriculares Bluetooth' => ['categoria' => 'Electrónica', 'precio_compra' => 25, 'precio_venta' => 45],

            // Alimentos
            'Arroz Blanco 1kg' => ['categoria' => 'Alimentos', 'precio_compra' => 1.5, 'precio_venta' => 2.5],
            'Aceite Vegetal 1L' => ['categoria' => 'Alimentos', 'precio_compra' => 2, 'precio_venta' => 3.5],
            'Azúcar 1kg' => ['categoria' => 'Alimentos', 'precio_compra' => 1, 'precio_venta' => 1.8],
            'Café Molido 500g' => ['categoria' => 'Alimentos', 'precio_compra' => 4, 'precio_venta' => 6.5],
            'Leche Entera 1L' => ['categoria' => 'Alimentos', 'precio_compra' => 1.2, 'precio_venta' => 2],

            // Ropa
            'Camiseta Básica' => ['categoria' => 'Ropa', 'precio_compra' => 5, 'precio_venta' => 12],
            'Pantalón Jean' => ['categoria' => 'Ropa', 'precio_compra' => 15, 'precio_venta' => 30],
            'Zapatos Deportivos' => ['categoria' => 'Ropa', 'precio_compra' => 25, 'precio_venta' => 50],
            'Chaqueta de Invierno' => ['categoria' => 'Ropa', 'precio_compra' => 35, 'precio_venta' => 70],
            'Gorra Deportiva' => ['categoria' => 'Ropa', 'precio_compra' => 6, 'precio_venta' => 12],

            // Hogar
            'Juego de Sábanas Queen' => ['categoria' => 'Hogar', 'precio_compra' => 20, 'precio_venta' => 40],
            'Toallas de Baño x3' => ['categoria' => 'Hogar', 'precio_compra' => 12, 'precio_venta' => 25],
            'Set de Ollas 5 Piezas' => ['categoria' => 'Hogar', 'precio_compra' => 35, 'precio_venta' => 65],
            'Cortinas Blackout' => ['categoria' => 'Hogar', 'precio_compra' => 18, 'precio_venta' => 35],
            'Lámpara de Mesa' => ['categoria' => 'Hogar', 'precio_compra' => 15, 'precio_venta' => 28],

            // Deportes
            'Balón de Fútbol' => ['categoria' => 'Deportes', 'precio_compra' => 12, 'precio_venta' => 25],
            'Pesas Ajustables' => ['categoria' => 'Deportes', 'precio_compra' => 30, 'precio_venta' => 55],
            'Cuerda para Saltar' => ['categoria' => 'Deportes', 'precio_compra' => 5, 'precio_venta' => 10],
            'Colchoneta de Yoga' => ['categoria' => 'Deportes', 'precio_compra' => 15, 'precio_venta' => 28],
            'Botella Térmica' => ['categoria' => 'Deportes', 'precio_compra' => 8, 'precio_venta' => 15],

            // Belleza
            'Shampoo 400ml' => ['categoria' => 'Belleza', 'precio_compra' => 4, 'precio_venta' => 8],
            'Crema Facial 50ml' => ['categoria' => 'Belleza', 'precio_compra' => 10, 'precio_venta' => 18],
            'Set de Maquillaje' => ['categoria' => 'Belleza', 'precio_compra' => 25, 'precio_venta' => 45],
            'Perfume 100ml' => ['categoria' => 'Belleza', 'precio_compra' => 20, 'precio_venta' => 40],
            'Kit de Uñas' => ['categoria' => 'Belleza', 'precio_compra' => 8, 'precio_venta' => 15],

            // Juguetes
            'Muñeca Articulada' => ['categoria' => 'Juguetes', 'precio_compra' => 12, 'precio_venta' => 25],
            'Carros de Juguete x3' => ['categoria' => 'Juguetes', 'precio_compra' => 8, 'precio_venta' => 15],
            'Bloques de Construcción' => ['categoria' => 'Juguetes', 'precio_compra' => 15, 'precio_venta' => 30],
            'Rompecabezas 1000 pzs' => ['categoria' => 'Juguetes', 'precio_compra' => 10, 'precio_venta' => 18],
            'Pelota de Playa' => ['categoria' => 'Juguetes', 'precio_compra' => 3, 'precio_venta' => 6],

            // Libros
            'Novela Bestseller' => ['categoria' => 'Libros', 'precio_compra' => 8, 'precio_venta' => 15],
            'Libro de Cocina' => ['categoria' => 'Libros', 'precio_compra' => 12, 'precio_venta' => 22],
            'Atlas Mundial' => ['categoria' => 'Libros', 'precio_compra' => 18, 'precio_venta' => 32],
            'Diccionario Español' => ['categoria' => 'Libros', 'precio_compra' => 15, 'precio_venta' => 28],
            'Revista Mensual' => ['categoria' => 'Libros', 'precio_compra' => 3, 'precio_venta' => 5],

            // Ferretería
            'Martillo de Carpintero' => ['categoria' => 'Ferretería', 'precio_compra' => 10, 'precio_venta' => 18],
            'Destornillador Set' => ['categoria' => 'Ferretería', 'precio_compra' => 12, 'precio_venta' => 22],
            'Cinta Métrica 5m' => ['categoria' => 'Ferretería', 'precio_compra' => 5, 'precio_venta' => 9],
            'Taladro Eléctrico' => ['categoria' => 'Ferretería', 'precio_compra' => 45, 'precio_venta' => 85],
            'Pintura Látex 1 Galón' => ['categoria' => 'Ferretería', 'precio_compra' => 15, 'precio_venta' => 28],

            // Oficina
            'Resma de Papel A4' => ['categoria' => 'Oficina', 'precio_compra' => 4, 'precio_venta' => 7],
            'Bolígrafos x12' => ['categoria' => 'Oficina', 'precio_compra' => 3, 'precio_venta' => 6],
            'Archivador de Palanca' => ['categoria' => 'Oficina', 'precio_compra' => 2, 'precio_venta' => 4],
            'Calculadora Científica' => ['categoria' => 'Oficina', 'precio_compra' => 15, 'precio_venta' => 28],
            'Agenda 2025' => ['categoria' => 'Oficina', 'precio_compra' => 5, 'precio_venta' => 10],
        ];

        $count = 0;
        foreach ($productosBase as $nombre => $data) {
            // Buscar categoría por nombre
            $categoria = Categoria::where('empresa_id', 1)
                ->where('nombre', $data['categoria'])
                ->first();

            if (!$categoria) {
                continue;
            }

            // Generar URL de imagen usando placeholder con color aleatorio
            $colores = ['0066CC', 'FF6B6B', '4ECDC4', 'FFE66D', '95E1D3', 'FF8B94', '6C5CE7', 'FD79A8'];
            $colorAleatorio = $faker->randomElement($colores);
            $nombreImagen = urlencode(substr($nombre, 0, 20));
            $imagenUrl = "https://via.placeholder.com/400x400/{$colorAleatorio}/FFFFFF?text={$nombreImagen}";

            $producto = Producto::create([
                'nombre' => $nombre,
                'descripcion' => $faker->sentence(10),
                'sku' => strtoupper($faker->bothify('SKU-####??')),
                'codigo_barras' => $faker->ean13(),
                'precio_compra' => $data['precio_compra'],
                'precio_venta' => $data['precio_venta'],
                'precio_mayoreo' => $data['precio_venta'] * 0.85, // 15% descuento
                'stock_minimo' => $faker->numberBetween(5, 20),
                'stock_actual' => $faker->numberBetween(20, 100),
                'unidad_medida' => $faker->randomElement($unidadesMedida),
                'imagen' => $imagenUrl,
                'activo' => $faker->boolean(90), // 90% activos
                'empresa_id' => 1,
            ]);

            // Asignar 1-3 categorías aleatorias (incluyendo la principal)
            $categoriasAleatorias = $faker->randomElements($categorias, $faker->numberBetween(1, 3));
            if (!in_array($categoria->id, $categoriasAleatorias)) {
                $categoriasAleatorias[] = $categoria->id;
            }
            $producto->categorias()->sync($categoriasAleatorias);

            $count++;
        }

        $this->command->info("{$count} productos creados exitosamente para empresa_id = 1");
    }
}
