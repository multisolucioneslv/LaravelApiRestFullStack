<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear empresa por default: Yapame
        Empresa::create([
            'nombre' => 'Yapame',
            'email' => 'contacto@yapame.com',
            'direccion' => 'Ciudad de prueba',
            'zona_horaria' => 'America/Los_Angeles',
            'activo' => true,
        ]);

        echo "✅ Empresa default 'Yapame' creada exitosamente\n";
    }
}
