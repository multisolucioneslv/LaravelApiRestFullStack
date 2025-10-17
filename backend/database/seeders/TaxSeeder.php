<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tax;
class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            ['nombre' => 'I.V.A', 'porcentaje' => 13, 'descripcion' => 'Impuesto al valor agregado - IVA - El Salvador', 'empresa_id' => 1]
        ];
        foreach ($taxes as $tax) {
            Tax::create($tax);
        }
    }
}
