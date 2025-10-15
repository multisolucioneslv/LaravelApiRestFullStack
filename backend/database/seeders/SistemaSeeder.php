<?php

namespace Database\Seeders;

use App\Models\Sistema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sistema::create([
            'nombre' => 'Sistema por default',
            'version' => '1.0.0',
            'configuracion' => json_encode([
                'timezone' => 'America/Los_Angeles',
                'language' => 'es',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i:s',
            ]),
            'logo' => null,
            'activo' => true,
        ]);
    }
}
