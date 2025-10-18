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
            TaxSeeder::class,         // 7. Taxes
            RoleSeeder::class,        // 8. Roles (SuperAdmin, Administrador, etc.)
            PermissionsSeeder::class, // 9. Permisos del sistema
            SuperAdminSeeder::class,  // 10. Usuario SuperAdmin (jscothserver)
            CategoriaSeeder::class,   // 11. Categorías de productos
            ProductoSeeder::class,    // 12. Productos de ejemplo (con imágenes)
            BodegaSeeder::class,      // 13. Bodegas por empresa
            GaleriaSeeder::class,     // 14. Galerías de imágenes para productos
            InventarioSeeder::class,  // 15. Inventarios (productos en bodegas)
            CotizacionSeeder::class,  // 16. Cotizaciones con detalles
            VentaSeeder::class,       // 17. Ventas vinculadas a cotizaciones
            PedidoSeeder::class,      // 18. Pedidos con estados progresivos
        ]);
    }
}
