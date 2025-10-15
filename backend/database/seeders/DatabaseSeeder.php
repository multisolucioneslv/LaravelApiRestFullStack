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
            GenderSeeder::class,      // 2. Genders (Masculino, Femenino, Otro)
            CurrencySeeder::class,    // 3. Currencies (USD, MXN, EUR, BOB)
            EmpresaSeeder::class,     // 4. Empresa default (Yapame)
            RoleSeeder::class,        // 5. Roles (SuperAdmin, Administrador, etc.)
            PermissionsSeeder::class, // 6. Permisos del sistema
            SuperAdminSeeder::class,  // 7. Usuario SuperAdmin (jscothserver)
        ]);
    }
}
