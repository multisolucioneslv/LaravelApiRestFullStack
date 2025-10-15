<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Moneda>
 */
class MonedaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currencies = [
            ['codigo' => 'USD', 'nombre' => 'Dólar Estadounidense', 'simbolo' => '$', 'tasa_cambio' => 1.0000],
            ['codigo' => 'EUR', 'nombre' => 'Euro', 'simbolo' => '€', 'tasa_cambio' => 0.9200],
            ['codigo' => 'MXN', 'nombre' => 'Peso Mexicano', 'simbolo' => 'MX$', 'tasa_cambio' => 17.5000],
            ['codigo' => 'COP', 'nombre' => 'Peso Colombiano', 'simbolo' => 'COL$', 'tasa_cambio' => 4200.0000],
        ];

        $currency = fake()->randomElement($currencies);

        return [
            'codigo' => $currency['codigo'],
            'nombre' => $currency['nombre'],
            'simbolo' => $currency['simbolo'],
            'tasa_cambio' => $currency['tasa_cambio'],
            'activo' => true,
        ];
    }
}
