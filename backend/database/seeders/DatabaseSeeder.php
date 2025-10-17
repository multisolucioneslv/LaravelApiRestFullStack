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
            SistemaSeeder::class,     // 1. Sistema por default
            GenderSeeder::class,      // 2. Genders (Masculino, Femenino, Otro)
            CurrencySeeder::class,    // 3. Currencies (USD, MXN, EUR, BOB)
            PhoneSeeder::class,       // 4. Phones (teléfonos del sistema)
            ChatidSeeder::class,      // 5. Chatids (chatids del sistema)
            EmpresaSeeder::class,     // 6. Empresa default (Yapame)
            TaxSeeder::class,         // Taxes  
            RoleSeeder::class,        // 7. Roles (SuperAdmin, Administrador, etc.)
            PermissionsSeeder::class, // 8. Permisos del sistema
            SuperAdminSeeder::class,  // 9. Usuario SuperAdmin (jscothserver)
            CategoriaSeeder::class,   // 10. Categorías de productos
            ProductoSeeder::class,    // 11. Productos de ejemplo
        ]);
    }
}
