<?php

namespace App\Http\Requests\Chatid;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreChatidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idtelegram' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'idtelegram.required' => 'El campo ID de Telegram es requerido.',
            'idtelegram.string' => 'El ID de Telegram debe ser una cadena de texto.',
            'idtelegram.max' => 'El ID de Telegram no puede exceder los :max caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
