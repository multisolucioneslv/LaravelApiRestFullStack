<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
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
            [
                'codigo' => 'BOB',
                'nombre' => 'Boliviano',
                'simbolo' => 'Bs',
                'tasa_cambio' => 6.9100,
                'activo' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['codigo' => $currency['codigo']],
                $currency
            );
        }
    }
}
