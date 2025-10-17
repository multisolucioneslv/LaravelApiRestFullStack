<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->words(3, true),
            'descripcion' => fake()->sentence(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'codigo_barras' => fake()->unique()->ean13(),
            'precio_compra' => fake()->randomFloat(2, 10, 100),
            'precio_venta' => fake()->randomFloat(2, 50, 200),
            'precio_mayoreo' => fake()->randomFloat(2, 40, 150),
            'stock_minimo' => fake()->numberBetween(5, 20),
            'stock_actual' => fake()->numberBetween(20, 100),
            'unidad_medida' => fake()->randomElement(['pieza', 'kg', 'litro', 'metro', 'caja']),
            'imagen' => null,
            'activo' => true,
            'empresa_id' => Empresa::factory(),
        ];
    }

    /**
     * Estado: Producto inactivo
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Estado: Producto con stock bajo
     */
    public function bajoStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_minimo' => 20,
            'stock_actual' => fake()->numberBetween(1, 15),
        ]);
    }

    /**
     * Estado: Producto sin stock
     */
    public function sinStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_actual' => 0,
        ]);
    }
}
