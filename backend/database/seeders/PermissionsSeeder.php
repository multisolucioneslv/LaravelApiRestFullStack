<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetear caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir todos los módulos y sus permisos CRUD
        $modules = [
            'users' => 'Usuarios',
            'roles' => 'Roles',
            'permissions' => 'Permisos',
            'empresas' => 'Empresas',
            'sistemas' => 'Sistemas',
            'bodegas' => 'Bodegas',
            'inventarios' => 'Inventarios',
            'monedas' => 'Monedas',
            'taxes' => 'Impuestos',
            'galerias' => 'Galerías',
            'cotizaciones' => 'Cotizaciones',
            'ventas' => 'Ventas',
            'pedidos' => 'Pedidos',
            'sexes' => 'Sexos',
            'telefonos' => 'Teléfonos',
            'chatids' => 'Chat IDs',
            'rutas' => 'Rutas API',
            'settings' => 'Configuraciones',
            'reports' => 'Reportes',
            'productos' => 'Productos',
            'categorias' => 'Categorías',
        ];

        $actions = ['index', 'show', 'store', 'update', 'destroy'];

        // Crear permisos para cada módulo
        foreach ($modules as $module => $description) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$action}"],
                    ['guard_name' => 'api']
                );
            }
        }

        // Permisos especiales
        $specialPermissions = [
            'users.change-password' => 'Cambiar contraseña de usuario',
            'users.delete-avatar' => 'Eliminar avatar de usuario',
            'users.bulk-delete' => 'Eliminar usuarios en lote',
            'empresa.config.view' => 'Ver configuración de empresa',
            'empresa.config.update' => 'Actualizar configuración de empresa',
            'dashboard.view' => 'Ver dashboard',
            'profile.view' => 'Ver perfil',
            'profile.edit' => 'Editar perfil',
            'productos.create' => 'Crear productos',
            'productos.update' => 'Actualizar productos',
            'productos.delete' => 'Eliminar productos',
            'productos.restore' => 'Restaurar productos eliminados',
            'productos.stock' => 'Actualizar stock de productos',
            'productos.categorias.sync' => 'Sincronizar categorías de productos',
            'categorias.create' => 'Crear categorías',
            'categorias.update' => 'Actualizar categorías',
            'categorias.delete' => 'Eliminar categorías',
            'categorias.restore' => 'Restaurar categorías eliminadas',
            'categorias.productos' => 'Listar productos de categoría',
        ];

        foreach ($specialPermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'api']
            );
        }

        // Crear roles si no existen
        $superAdmin = Role::firstOrCreate(
            ['name' => 'SuperAdmin'],
            ['guard_name' => 'api']
        );

        $admin = Role::firstOrCreate(
            ['name' => 'Administrador'],
            ['guard_name' => 'api']
        );

        $supervisor = Role::firstOrCreate(
            ['name' => 'Supervisor'],
            ['guard_name' => 'api']
        );

        $vendedor = Role::firstOrCreate(
            ['name' => 'Vendedor'],
            ['guard_name' => 'api']
        );

        $usuario = Role::firstOrCreate(
            ['name' => 'Usuario'],
            ['guard_name' => 'api']
        );

        $contabilidad = Role::firstOrCreate(
            ['name' => 'Contabilidad'],
            ['guard_name' => 'api']
        );

        // SuperAdmin tiene TODOS los permisos
        $superAdmin->syncPermissions(Permission::all());

        // Administrador tiene permisos de gestión de su empresa
        $admin->syncPermissions([
            // Usuarios
            'users.index', 'users.show', 'users.store', 'users.update', 'users.destroy',
            'users.change-password', 'users.delete-avatar',

            // Bodegas
            'bodegas.index', 'bodegas.show', 'bodegas.store', 'bodegas.update', 'bodegas.destroy',

            // Inventarios
            'inventarios.index', 'inventarios.show', 'inventarios.store', 'inventarios.update', 'inventarios.destroy',

            // Productos y Categorías (todos los permisos)
            'productos.index', 'productos.show', 'productos.create', 'productos.update', 'productos.delete', 'productos.restore', 'productos.stock', 'productos.categorias.sync',
            'categorias.index', 'categorias.show', 'categorias.create', 'categorias.update', 'categorias.delete', 'categorias.restore', 'categorias.productos',

            // Ventas y cotizaciones
            'cotizaciones.index', 'cotizaciones.show', 'cotizaciones.store', 'cotizaciones.update', 'cotizaciones.destroy',
            'ventas.index', 'ventas.show', 'ventas.store', 'ventas.update', 'ventas.destroy',
            'pedidos.index', 'pedidos.show', 'pedidos.store', 'pedidos.update', 'pedidos.destroy',

            // Configuración
            'empresa.config.view', 'empresa.config.update',
            'settings.index', 'settings.show',

            // Reportes
            'reports.index', 'reports.show',
            'dashboard.view',

            // Perfil
            'profile.view', 'profile.edit',
        ]);

        // Supervisor - puede ver, crear y editar productos (NO eliminar)
        $supervisor->syncPermissions([
            // Productos (sin eliminar)
            'productos.index', 'productos.show', 'productos.create', 'productos.update',
            'categorias.index', 'categorias.show', 'categorias.create', 'categorias.update',

            // Inventarios
            'inventarios.index', 'inventarios.show', 'inventarios.store', 'inventarios.update',

            // Ventas
            'cotizaciones.index', 'cotizaciones.show', 'cotizaciones.store', 'cotizaciones.update',
            'ventas.index', 'ventas.show', 'ventas.store',
            'pedidos.index', 'pedidos.show', 'pedidos.store', 'pedidos.update',

            // Dashboard y perfil
            'dashboard.view',
            'profile.view', 'profile.edit',
        ]);

        // Vendedor - solo lectura de productos, actualizar stock y operaciones de venta
        $vendedor->syncPermissions([
            // Productos (solo lectura y actualizar stock)
            'productos.index', 'productos.show', 'productos.stock',
            'categorias.index', 'categorias.show',

            // Ventas
            'cotizaciones.index', 'cotizaciones.show', 'cotizaciones.store', 'cotizaciones.update',
            'ventas.index', 'ventas.show', 'ventas.store',
            'pedidos.index', 'pedidos.show', 'pedidos.store', 'pedidos.update',

            // Inventarios (solo lectura)
            'inventarios.index', 'inventarios.show',

            // Dashboard y perfil
            'dashboard.view',
            'profile.view', 'profile.edit',
        ]);

        // Usuario - solo lectura de productos
        $usuario->syncPermissions([
            // Productos (solo lista)
            'productos.index',

            // Dashboard y perfil
            'dashboard.view',
            'profile.view', 'profile.edit',
        ]);

        // Contabilidad - solo lectura de productos y acceso a reportes
        $contabilidad->syncPermissions([
            // Productos (lectura)
            'productos.index', 'productos.show',
            'categorias.index', 'categorias.show',

            // Ventas (lectura)
            'cotizaciones.index', 'cotizaciones.show',
            'ventas.index', 'ventas.show',
            'pedidos.index', 'pedidos.show',

            // Reportes
            'reports.index', 'reports.show',

            // Dashboard y perfil
            'dashboard.view',
            'profile.view', 'profile.edit',
        ]);

        $this->command->info('✓ Permisos y roles creados exitosamente');
        $this->command->info('✓ Total de permisos: ' . Permission::count());
        $this->command->info('✓ Total de roles: ' . Role::count());
    }
}
