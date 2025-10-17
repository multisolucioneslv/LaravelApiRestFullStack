<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Electrónica',
                'descripcion' => 'Productos electrónicos y tecnología',
                'icono' => 'fas fa-laptop',
                'color' => '#3B82F6',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Alimentos',
                'descripcion' => 'Productos alimenticios y bebidas',
                'icono' => 'fas fa-utensils',
                'color' => '#10B981',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Ropa',
                'descripcion' => 'Ropa y accesorios',
                'icono' => 'fas fa-tshirt',
                'color' => '#8B5CF6',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Hogar',
                'descripcion' => 'Artículos para el hogar',
                'icono' => 'fas fa-home',
                'color' => '#F59E0B',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Deportes',
                'descripcion' => 'Artículos deportivos y fitness',
                'icono' => 'fas fa-dumbbell',
                'color' => '#EF4444',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Belleza',
                'descripcion' => 'Productos de belleza y cuidado personal',
                'icono' => 'fas fa-spa',
                'color' => '#EC4899',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Juguetes',
                'descripcion' => 'Juguetes y juegos',
                'icono' => 'fas fa-gamepad',
                'color' => '#F97316',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Libros',
                'descripcion' => 'Libros y material educativo',
                'icono' => 'fas fa-book',
                'color' => '#06B6D4',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Ferretería',
                'descripcion' => 'Herramientas y materiales de construcción',
                'icono' => 'fas fa-hammer',
                'color' => '#6B7280',
                'activo' => true,
                'empresa_id' => 1,
            ],
            [
                'nombre' => 'Oficina',
                'descripcion' => 'Material de oficina y papelería',
                'icono' => 'fas fa-briefcase',
                'color' => '#14B8A6',
                'activo' => true,
                'empresa_id' => 1,
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }

        $this->command->info('10 categorías creadas exitosamente para empresa_id = 1');
    }
}
