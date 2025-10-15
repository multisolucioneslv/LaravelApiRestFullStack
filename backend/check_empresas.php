<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$empresas = App\Models\Empresa::all();

echo "\n";
echo "========================================\n";
echo "  EMPRESAS EN LA BASE DE DATOS\n";
echo "========================================\n";
echo "Total: " . $empresas->count() . "\n\n";

if ($empresas->isEmpty()) {
    echo "⚠️  No hay empresas en la base de datos\n\n";
} else {
    foreach ($empresas as $empresa) {
        echo "ID: {$empresa->id}\n";
        echo "Nombre: {$empresa->nombre}\n";
        echo "Email: {$empresa->email}\n";
        echo "Activo: " . ($empresa->activo ? 'Sí' : 'No') . "\n";
        echo "----------------------------------------\n";
    }
}

echo "\n";
