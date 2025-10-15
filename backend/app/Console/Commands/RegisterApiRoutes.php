<?php

namespace App\Console\Commands;

use App\Models\Ruta;
use App\Models\Sistema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class RegisterApiRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:register {--sistema-id=1 : ID del sistema al que pertenecen las rutas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registra todas las rutas API del sistema en la tabla rutas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sistemaId = $this->option('sistema-id');

        // Verificar que el sistema existe
        $sistema = Sistema::find($sistemaId);
        if (!$sistema) {
            $this->error("El sistema con ID {$sistemaId} no existe.");
            $this->info("Creando sistema por defecto...");

            $sistema = Sistema::create([
                'nombre' => 'BackendProfesional API',
                'descripcion' => 'Sistema ERP - API REST',
                'version' => '1.0.0',
                'activo' => true,
            ]);

            $this->info("Sistema creado con ID: {$sistema->id}");
            $sistemaId = $sistema->id;
        }

        $this->info("Registrando rutas del sistema: {$sistema->nombre}");
        $this->newLine();

        // Obtener todas las rutas API
        $routes = Route::getRoutes();
        $registradas = 0;
        $actualizadas = 0;
        $saltadas = 0;

        $progressBar = $this->output->createProgressBar(count($routes));
        $progressBar->start();

        foreach ($routes as $route) {
            $progressBar->advance();

            // Solo procesar rutas API (que empiecen con 'api/')
            $uri = $route->uri();
            if (!str_starts_with($uri, 'api/')) {
                $saltadas++;
                continue;
            }

            // Obtener información de la ruta
            $method = implode('|', $route->methods());
            $action = $route->getActionName();

            // Extraer controlador y acción
            $controlador = null;
            $accion = null;

            if (str_contains($action, '@')) {
                [$controlador, $accion] = explode('@', $action);
            } elseif (str_contains($action, '::')) {
                $parts = explode('::', $action);
                $controlador = $parts[0] ?? null;
                $accion = $parts[1] ?? null;
            }

            // Obtener middleware
            $middleware = $route->middleware();

            // Determinar descripción basada en la acción
            $descripcion = $this->getDescripcion($uri, $method, $accion);

            // Buscar si la ruta ya existe
            $rutaExistente = Ruta::where('ruta', '/' . $uri)
                ->where('metodo', $method)
                ->first();

            $datos = [
                'sistema_id' => $sistemaId,
                'ruta' => '/' . $uri,
                'metodo' => $method,
                'descripcion' => $descripcion,
                'controlador' => $controlador,
                'accion' => $accion,
                'middleware' => $middleware,
                'activo' => true,
            ];

            if ($rutaExistente) {
                $rutaExistente->update($datos);
                $actualizadas++;
            } else {
                Ruta::create($datos);
                $registradas++;
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->info("✅ Proceso completado!");
        $this->newLine();
        $this->table(
            ['Estadística', 'Cantidad'],
            [
                ['Rutas nuevas registradas', $registradas],
                ['Rutas actualizadas', $actualizadas],
                ['Rutas saltadas (no API)', $saltadas],
                ['Total procesadas', count($routes)],
            ]
        );

        $this->newLine();
        $this->info("💡 Tip: Puedes ver las rutas registradas en el módulo 'Rutas API' del sistema.");

        return Command::SUCCESS;
    }

    /**
     * Genera una descripción automática para la ruta
     */
    private function getDescripcion(string $uri, string $method, ?string $accion): string
    {
        // Extraer el módulo de la URI
        $parts = explode('/', trim($uri, '/'));
        $modulo = $parts[1] ?? 'General';
        $modulo = ucfirst($modulo);

        // Determinar acción según el método y la acción del controlador
        if ($accion) {
            $acciones = [
                'index' => 'Listar',
                'store' => 'Crear',
                'show' => 'Ver detalle',
                'update' => 'Actualizar',
                'destroy' => 'Eliminar',
                'destroyBulk' => 'Eliminar múltiples',
                'login' => 'Iniciar sesión',
                'register' => 'Registrar usuario',
                'logout' => 'Cerrar sesión',
                'refresh' => 'Refrescar token',
                'me' => 'Usuario autenticado',
            ];

            $descripcionAccion = $acciones[$accion] ?? ucfirst($accion);
        } else {
            // Determinar por método HTTP
            $descripcionAccion = match(true) {
                str_contains($method, 'GET') => 'Consultar',
                str_contains($method, 'POST') => 'Crear',
                str_contains($method, 'PUT') || str_contains($method, 'PATCH') => 'Actualizar',
                str_contains($method, 'DELETE') => 'Eliminar',
                default => 'Procesar',
            };
        }

        return "{$descripcionAccion} {$modulo}";
    }
}
