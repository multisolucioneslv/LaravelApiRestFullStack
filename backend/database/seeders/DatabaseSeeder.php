<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Orden de ejecución de seeders (respetar dependencias)
        $this->call([
            SistemaSeeder::class,    // 1. Sistema por default
            SexSeeder::class,         // 2. Sexos (Masculino, Femenino)
            MonedaSeeder::class,      // 3. Monedas (USD, MXN, EUR)
            EmpresaSeeder::class,     // 4. Empresa default (Yapame)
            RoleSeeder::class,        // 5. Roles (SuperAdmin, Administrador, etc.)
            SuperAdminSeeder::class,  // 6. Usuario SuperAdmin (jscothserver)
        ]);
    }
}
