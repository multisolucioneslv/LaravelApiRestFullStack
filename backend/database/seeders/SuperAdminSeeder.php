<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario SuperAdmin con campos de texto directo
        $user = User::create([
            'usuario' => 'jscothserver',
            'name' => 'jscothserver',
            'email' => 'jscothserver@gmail.com',
            'password' => Hash::make('72900968'),
            'gender_id' => 1, // Masculino
            'telefono' => '(702)337-9581',  // Campo string directo
            'chatid' => '5332512577',       // Campo string directo
            'empresa_id' => 1, // Yapame (empresa por default)
            'avatar' => null,
            'cuenta' => 'jscothserver',
            'razon_suspendida' => null,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        // Asignar rol SuperAdmin
        $user->assignRole('SuperAdmin');

        echo "✅ Usuario SuperAdmin creado con empresa Yapame asignada\n";
    }
}
