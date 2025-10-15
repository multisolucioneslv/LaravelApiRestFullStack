<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genders = [
            ['sexo' => 'Masculino', 'inicial' => 'M'],
            ['sexo' => 'Femenino', 'inicial' => 'F'],
            ['sexo' => 'Otro', 'inicial' => 'O'],
        ];

        foreach ($genders as $gender) {
            Gender::firstOrCreate(
                ['inicial' => $gender['inicial']],
                $gender
            );
        }
    }
}
