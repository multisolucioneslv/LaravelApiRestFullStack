<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->words(2, true);

        return [
            'nombre' => ucfirst($nombre),
            'descripcion' => fake()->sentence(),
            'slug' => Str::slug($nombre),
            'icono' => fake()->randomElement(['fas fa-box', 'fas fa-tag', 'fas fa-shopping-cart', 'fas fa-laptop']),
            'color' => fake()->hexColor(),
            'activo' => true,
            'empresa_id' => Empresa::factory(),
        ];
    }

    /**
     * Estado: Categoría inactiva
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
