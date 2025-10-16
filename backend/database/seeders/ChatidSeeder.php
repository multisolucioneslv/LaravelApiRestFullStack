<?php

namespace Database\Seeders;

use App\Models\Chatid;
use Illuminate\Database\Seeder;

class ChatidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chatids = [
            ['idtelegram' => '5332512577'], // Chatid del usuario jscothserver
        ];

        foreach ($chatids as $chatid) {
            Chatid::firstOrCreate($chatid);
        }
    }
}
