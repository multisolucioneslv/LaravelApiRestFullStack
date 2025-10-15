<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('usuario', 'jscothserver')->first();

if (!$user) {
    echo "❌ Usuario jscothserver no encontrado\n";
    exit(1);
}

// Actualizar usuario con nuevos campos
$user->update([
    'telefono' => '(702)337-9581',
    'chatid' => '5332512577',
    'empresa_id' => 1, // Yapame
]);

echo "\n";
echo "========================================\n";
echo "  USUARIO SUPERADMIN ACTUALIZADO\n";
echo "========================================\n";
echo "Usuario: {$user->usuario}\n";
echo "Teléfono: {$user->telefono}\n";
echo "Chat ID: {$user->chatid}\n";
echo "Empresa ID: {$user->empresa_id}\n";
echo "========================================\n";
echo "✅ Usuario actualizado exitosamente\n\n";
