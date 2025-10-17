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
        $this->maxTokens = config('services.openai.max_tokens', env('OPENAI_MAX_TOKENS', 1500));
        $this->temperature = config('services.openai.temperature', env('OPENAI_TEMPERATURE', 0.7));
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
     * Generar un resumen o título basado en un texto
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
                    'content' => 'Eres un asistente que genera títulos cortos (máximo 50 caracteres) para conversaciones basados en el primer mensaje del usuario. Responde solo con el título, sin comillas ni puntuación adicional.'
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
            Log::error('Error generando título con OpenAI', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Preparar mensajes para enviar a OpenAI desde el historial de la conversación
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
