<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message'=>"Imgrese sus credenciales de acceso"
    ]);
});
// Rutas públicas de autenticación (con rate limiting)
Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    // Registro deshabilitado - solo SuperAdmin y Admin pueden crear usuarios
    // Route::post('/register', [AuthController::class, 'register']);
});

// Rutas protegidas (requieren autenticación JWT + validación de empresa)
Route::middleware(['auth:api', 'empresa'])->group(function () {
    // Rutas de autenticación
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:10,1');
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Módulo de Usuarios
    Route::prefix('users')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\UserController::class, 'index'])->middleware('permission:users.index');
        Route::post('/', [App\Http\Controllers\Api\UserController::class, 'store'])->middleware('permission:users.store');
        Route::get('/{id}', [App\Http\Controllers\Api\UserController::class, 'show'])->middleware('permission:users.show');
        Route::put('/{id}', [App\Http\Controllers\Api\UserController::class, 'update'])->middleware('permission:users.update');
        Route::delete('/{id}', [App\Http\Controllers\Api\UserController::class, 'destroy'])->middleware('permission:users.destroy');
        Route::delete('/bulk/delete', [App\Http\Controllers\Api\UserController::class, 'destroyBulk'])->middleware('permission:users.destroy');
        // Rutas específicas para perfil propio (sin requerir permisos - el controller valida que sea el propio usuario)
        Route::delete('/{id}/avatar', [App\Http\Controllers\Api\UserController::class, 'deleteAvatar']);
        Route::put('/{id}/password', [App\Http\Controllers\Api\UserController::class, 'updatePassword']);
    });

    // Módulo de Sistemas
    Route::prefix('sistemas')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\SistemaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\SistemaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\SistemaController::class, 'show']);
        Route::post('/{id}', [App\Http\Controllers\Api\SistemaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\SistemaController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\SistemaController::class, 'destroyBulk']);
    });

    // Módulo de Genders
    Route::prefix('genders')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\GenderController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\GenderController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\GenderController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\GenderController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\GenderController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\GenderController::class, 'destroyBulk']);
    });

    // Módulo de Currencies
    Route::prefix('currencies')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CurrencyController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\CurrencyController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\CurrencyController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\CurrencyController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\CurrencyController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\CurrencyController::class, 'destroyBulk']);
    });

    // Módulo de Teléfonos
    Route::prefix('telefonos')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\TelefonoController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\TelefonoController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\TelefonoController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\TelefonoController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\TelefonoController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\TelefonoController::class, 'destroyBulk']);
    });

    // Módulo de Chat IDs
    Route::prefix('chatids')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\ChatidController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\ChatidController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\ChatidController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\ChatidController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\ChatidController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\ChatidController::class, 'destroyBulk']);
    });

    // Módulo de Bodegas
    Route::prefix('bodegas')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\BodegaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\BodegaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\BodegaController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\BodegaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\BodegaController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\BodegaController::class, 'destroyBulk']);
    });

    // Módulo de Empresas
    Route::prefix('empresas')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\EmpresaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\EmpresaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\EmpresaController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\EmpresaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\EmpresaController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\EmpresaController::class, 'destroyBulk']);
    });

    // Módulo de Inventarios
    Route::prefix('inventarios')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\InventarioController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\InventarioController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\InventarioController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\InventarioController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\InventarioController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\InventarioController::class, 'destroyBulk']);
    });

    // Módulo de Impuestos (Taxes)
    Route::prefix('taxes')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\TaxController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\TaxController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\TaxController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\TaxController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\TaxController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\TaxController::class, 'destroyBulk']);
    });

    // Módulo de Galerías
    Route::prefix('galerias')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\GaleriaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\GaleriaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\GaleriaController::class, 'show']);
        Route::post('/{id}', [App\Http\Controllers\Api\GaleriaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\GaleriaController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\GaleriaController::class, 'destroyBulk']);
    });

    // Módulo de Cotizaciones
    Route::prefix('cotizaciones')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CotizacionController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\CotizacionController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\CotizacionController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\CotizacionController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\CotizacionController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\CotizacionController::class, 'destroyBulk']);
    });

    // Módulo de Ventas
    Route::prefix('ventas')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\VentaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\VentaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\VentaController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\VentaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\VentaController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\VentaController::class, 'destroyBulk']);
    });

    // Módulo de Pedidos
    Route::prefix('pedidos')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\PedidoController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\PedidoController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\PedidoController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\PedidoController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\PedidoController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\PedidoController::class, 'destroyBulk']);
    });

    // Módulo de Rutas API del Sistema
    Route::prefix('rutas')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\RutaController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\RutaController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\RutaController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\RutaController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\RutaController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\RutaController::class, 'destroyBulk']);
    });

    // Módulo de Configuraciones (Settings)
    Route::prefix('settings')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\SettingController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\SettingController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\SettingController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\SettingController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\SettingController::class, 'destroy']);
        Route::post('/bulk/delete', [App\Http\Controllers\Api\SettingController::class, 'destroyBulk']);
    });

    // Configuración de Empresa (para Admin - solo SU empresa)
    Route::prefix('empresa/configuracion')->middleware('permission:empresas.update')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\EmpresaConfiguracionController::class, 'show']);
        Route::post('/', [App\Http\Controllers\Api\EmpresaConfiguracionController::class, 'update']);
        Route::delete('/logo', [App\Http\Controllers\Api\EmpresaConfiguracionController::class, 'deleteLogo']);
        Route::delete('/favicon', [App\Http\Controllers\Api\EmpresaConfiguracionController::class, 'deleteFavicon']);
        Route::delete('/fondo-login', [App\Http\Controllers\Api\EmpresaConfiguracionController::class, 'deleteFondoLogin']);
    });

    // Módulo de Roles y Permisos
    Route::prefix('roles')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\RoleController::class, 'index'])->middleware('permission:roles.index');
        Route::post('/', [App\Http\Controllers\Api\RoleController::class, 'store'])->middleware('permission:roles.store');
        Route::get('/permissions', [App\Http\Controllers\Api\RoleController::class, 'allPermissions'])->middleware('permission:roles.index');
        Route::get('/{id}', [App\Http\Controllers\Api\RoleController::class, 'show'])->middleware('permission:roles.show');
        Route::put('/{id}', [App\Http\Controllers\Api\RoleController::class, 'update'])->middleware('permission:roles.update');
        Route::delete('/{id}', [App\Http\Controllers\Api\RoleController::class, 'destroy'])->middleware('permission:roles.destroy');
    });

    // Aquí irán más módulos del ERP
});
