<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Phone;
use App\Models\Chatid;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asignar teléfonos y chatids existentes a los usuarios
        $users = User::all();

        foreach ($users as $user) {
            // Asignar el primer teléfono como teléfono principal
            $phone = Phone::whereNull('phonable_id')->first();
            if ($phone) {
                $user->phone_id = $phone->id;
                $user->save();
            }

            // Asignar teléfonos adicionales como relación polimórfica
            $additionalPhones = Phone::whereNull('phonable_id')
                ->where('id', '!=', $phone?->id ?? 0)
                ->get();

            foreach ($additionalPhones as $additionalPhone) {
                $additionalPhone->phonable_type = User::class;
                $additionalPhone->phonable_id = $user->id;
                $additionalPhone->save();
            }

            // Asignar el primer chatid como chatid principal
            $chatid = Chatid::whereNull('chatable_id')->first();
            if ($chatid) {
                $user->chatid_id = $chatid->id;
                $user->save();
            }

            // Asignar chatids adicionales como relación polimórfica
            $additionalChatids = Chatid::whereNull('chatable_id')
                ->where('id', '!=', $chatid?->id ?? 0)
                ->get();

            foreach ($additionalChatids as $additionalChatid) {
                $additionalChatid->chatable_type = User::class;
                $additionalChatid->chatable_id = $user->id;
                $additionalChatid->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Desasignar teléfonos y chatids de los usuarios
        $users = User::all();

        foreach ($users as $user) {
            $user->phone_id = null;
            $user->chatid_id = null;
            $user->save();
        }

        // Limpiar campos polimórficos
        Phone::query()->update([
            'phonable_type' => null,
            'phonable_id' => null,
        ]);

        Chatid::query()->update([
            'chatable_type' => null,
            'chatable_id' => null,
        ]);
    }
};
