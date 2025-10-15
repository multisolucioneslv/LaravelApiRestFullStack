<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'telefono_id' => \App\Models\Phone::factory(),
            'currency_id' => \App\Models\Currency::factory(),
            'email' => fake()->unique()->companyEmail(),
            'direccion' => fake()->address(),
            'logo' => null,
            'favicon' => null,
            'fondo_login' => null,
            'zona_horaria' => 'America/Los_Angeles',
            'horarios' => [
                'lunes' => ['inicio' => '09:00', 'fin' => '18:00'],
                'martes' => ['inicio' => '09:00', 'fin' => '18:00'],
                'miercoles' => ['inicio' => '09:00', 'fin' => '18:00'],
                'jueves' => ['inicio' => '09:00', 'fin' => '18:00'],
                'viernes' => ['inicio' => '09:00', 'fin' => '18:00'],
            ],
            'activo' => true,
        ];
    }
}
