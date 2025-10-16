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
        // Crear usuario SuperAdmin
        $user = User::create([
            'usuario' => 'jscothserver',
            'name' => 'jscothserver',
            'email' => 'jscothserver@gmail.com',
            'password' => Hash::make('72900968'),
            'gender_id' => 1, // Masculino (debe existir en tabla genders)
            'phone_id' => 1,  // FK al teléfono (702)337-9581 (debe existir en tabla phones)
            'chatid_id' => 1, // FK al chatid 5332512577 (debe existir en tabla chatids)
            'empresa_id' => 1, // Yapame (debe existir en tabla empresas)
            'avatar' => null,
            'cuenta' => 'activada', // SuperAdmin siempre tiene cuenta activada
            'razon_suspendida' => null,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        // Asignar rol SuperAdmin
        $user->assignRole('SuperAdmin');

        echo "✅ Usuario SuperAdmin creado con empresa Yapame asignada\n";
    }
}
