<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AIPermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Crear permiso para usar AI Chat
        $permission = Permission::firstOrCreate(
            ['name' => 'use-ai-chat'],
            ['guard_name' => 'api']
        );

        // SuperAdmin tiene todos los permisos
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
        }

        // Por defecto, Administrador también puede usar AI
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        $this->command->info('✅ Permiso "use-ai-chat" creado y asignado a SuperAdmin y Administrador');
    }
}
