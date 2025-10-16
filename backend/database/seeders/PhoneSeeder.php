<?php

namespace Database\Seeders;

use App\Models\Phone;
use Illuminate\Database\Seeder;

class PhoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phones = [
            ['telefono' => '(702)337-9581'], // Teléfono del usuario jscothserver
        ];

        foreach ($phones as $phone) {
            Phone::firstOrCreate($phone);
        }
    }
}
