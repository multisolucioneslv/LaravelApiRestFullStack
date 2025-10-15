<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'SuperAdmin',
                'guard_name' => 'api',
            ],
            [
                'name' => 'Administrador',
                'guard_name' => 'api',
            ],
            [
                'name' => 'Supervisor',
                'guard_name' => 'api',
            ],
            [
                'name' => 'Vendedor',
                'guard_name' => 'api',
            ],
            [
                'name' => 'Usuario',
                'guard_name' => 'api',
            ],
            [
                'name' => 'Contabilidad',
                'guard_name' => 'api',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
