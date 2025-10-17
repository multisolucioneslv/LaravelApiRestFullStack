<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nWebhookService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.n8n.base_url', env('N8N_BASE_URL'));
        $this->apiKey = config('services.n8n.api_key', env('N8N_API_KEY'));
    }

    /**
     * Llamar a un webhook de N8N
     *
     * @param string $webhookPath Ruta del webhook (ej: 'webhook/consultar-usuarios')
     * @param array $data Datos a enviar
     * @return array
     */
    public function callWebhook(string $webhookPath, array $data = [])
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($webhookPath, '/');

            $response = Http::withHeaders([
                'X-N8N-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, $data);

            if ($response->failed()) {
                Log::error('N8N Webhook Error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Error al llamar al webhook de N8N: ' . $response->status(),
                ];
            }

            return [
                'success' => true,
                'data' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('N8N Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Consultar usuarios
     *
     * @param array $filters Filtros de búsqueda
     * @return array
     */
    public function consultarUsuarios(array $filters = [])
    {
        return $this->callWebhook('webhook/consultar-usuarios', $filters);
    }

    /**
     * Consultar empresas
     *
     * @param array $filters Filtros de búsqueda
     * @return array
     */
    public function consultarEmpresas(array $filters = [])
    {
        return $this->callWebhook('webhook/consultar-empresas', $filters);
    }

    /**
     * Consultar productos
     *
     * @param array $filters Filtros de búsqueda
     * @return array
     */
    public function consultarProductos(array $filters = [])
    {
        return $this->callWebhook('webhook/consultar-productos', $filters);
    }

    /**
     * Consultar proveedores
     *
     * @param array $filters Filtros de búsqueda
     * @return array
     */
    public function consultarProveedores(array $filters = [])
    {
        return $this->callWebhook('webhook/consultar-proveedores', $filters);
    }

    /**
     * Consultar categorías
     *
     * @param array $filters Filtros de búsqueda
     * @return array
     */
    public function consultarCategorias(array $filters = [])
    {
        return $this->callWebhook('webhook/consultar-categorias', $filters);
    }

    /**
     * Enviar mensaje a un usuario o cliente
     *
     * @param array $messageData Datos del mensaje (destinatario, contenido, etc.)
     * @return array
     */
    public function enviarMensaje(array $messageData)
    {
        return $this->callWebhook('webhook/enviar-mensaje', $messageData);
    }

    /**
     * Ejecutar una acción personalizada en N8N
     *
     * @param string $action Nombre de la acción
     * @param array $data Datos para la acción
     * @return array
     */
    public function ejecutarAccion(string $action, array $data = [])
    {
        return $this->callWebhook('webhook/accion-personalizada', [
            'action' => $action,
            'data' => $data,
        ]);
    }
}
