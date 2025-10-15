<?php

namespace Database\Seeders;

use App\Models\Moneda;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonedaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $monedas = [
            [
                'codigo' => 'USD',
                'nombre' => 'Dólar Estadounidense',
                'simbolo' => '$',
                'tasa_cambio' => 1.0000,
                'activo' => true,
            ],
            [
                'codigo' => 'MXN',
                'nombre' => 'Peso Mexicano',
                'simbolo' => '$',
                'tasa_cambio' => 17.0000,
                'activo' => true,
            ],
            [
                'codigo' => 'EUR',
                'nombre' => 'Euro',
                'simbolo' => '€',
                'tasa_cambio' => 0.9200,
                'activo' => true,
            ],
        ];

        foreach ($monedas as $moneda) {
            Moneda::create($moneda);
        }
    }
}
