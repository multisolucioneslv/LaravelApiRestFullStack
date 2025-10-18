<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $apiKey;
    protected $model;
    protected $maxTokens;
    protected $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
        $this->model = config('services.openai.model', env('OPENAI_MODEL', 'gpt-4'));
        $this->maxTokens = (int) config('services.openai.max_tokens', env('OPENAI_MAX_TOKENS', 1500));
        $this->temperature = (float) config('services.openai.temperature', env('OPENAI_TEMPERATURE', 0.7));
    }

    /**
     * Configurar credenciales personalizadas de OpenAI (para multi-tenant)
     */
    public function setCustomCredentials($apiKey, $model = null, $maxTokens = null, $temperature = null)
    {
        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
        if ($model) {
            $this->model = $model;
        }
        if ($maxTokens) {
            $this->maxTokens = $maxTokens;
        }
        if ($temperature) {
            $this->temperature = $temperature;
        }
    }

    /**
     * Enviar mensajes a OpenAI y obtener respuesta
     *
     * @param array $messages Array de mensajes en formato OpenAI
     * @return array
     */
    public function chat(array $messages)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Error al comunicarse con OpenAI: ' . $response->body());
            }

            $data = $response->json();

            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? '',
                'usage' => $data['usage'] ?? null,
                'model' => $data['model'] ?? $this->model,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Service Exception', [
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
     * Enviar mensajes a OpenAI con Function Calling
     *
     * @param array $messages Array de mensajes en formato OpenAI
     * @param array $functions Array de definiciones de funciones
     * @return array
     */
    public function chatWithFunctions(array $messages, array $functions)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'functions' => $functions,
                'function_call' => 'auto',
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI API Error (Function Calling)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Error al comunicarse con OpenAI: ' . $response->body());
            }

            $data = $response->json();
            $choice = $data['choices'][0] ?? null;

            if (!$choice) {
                throw new \Exception('No se recibió respuesta de OpenAI');
            }

            $result = [
                'success' => true,
                'usage' => $data['usage'] ?? null,
                'model' => $data['model'] ?? $this->model,
            ];

            // Si OpenAI decidió llamar a una función
            if (isset($choice['message']['function_call'])) {
                $result['function_call'] = [
                    'name' => $choice['message']['function_call']['name'],
                    'arguments' => json_decode($choice['message']['function_call']['arguments'], true),
                ];
            } else {
                // Respuesta normal sin llamada a función
                $result['content'] = $choice['message']['content'] ?? '';
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('OpenAI Service Exception (Function Calling)', [
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
     * Detectar intención usando OpenAI (para modo Double Call)
     *
     * @param string $userMessage
     * @return array
     */
    public function detectIntent(string $userMessage)
    {
        try {
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente que analiza preguntas de usuarios y detecta su intención. Responde SOLO con una palabra que represente la categoría de la consulta: ventas, productos, usuarios, empresas, proveedores, categorias, general. No agregues explicaciones adicionales.',
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage,
                ],
            ];

            $response = $this->chat($messages);

            if ($response['success']) {
                $intent = strtolower(trim($response['content']));
                return [
                    'success' => true,
                    'intent' => $intent,
                    'usage' => $response['usage'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'No se pudo detectar la intención',
            ];

        } catch (\Exception $e) {
            Log::error('Error detectando intención con OpenAI', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generar un resumen o t�tulo basado en un texto
     *
     * @param string $text
     * @return string|null
     */
    public function generateTitle(string $text)
    {
        try {
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente que genera t�tulos cortos (m�ximo 50 caracteres) para conversaciones basados en el primer mensaje del usuario. Responde solo con el t�tulo, sin comillas ni puntuaci�n adicional.'
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ];

            $response = $this->chat($messages);

            if ($response['success']) {
                return trim($response['content']);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error generando t�tulo con OpenAI', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Preparar mensajes para enviar a OpenAI desde el historial de la conversaci�n
     *
     * @param \Illuminate\Database\Eloquent\Collection $messages
     * @return array
     */
    public function prepareMessagesFromConversation($messages)
    {
        return $messages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
            ];
        })->toArray();
    }

    /**
     * Crear mensaje de sistema con instrucciones personalizadas
     *
     * @param string $instructions
     * @return array
     */
    public function createSystemMessage(string $instructions)
    {
        return [
            'role' => 'system',
            'content' => $instructions,
        ];
    }
}
