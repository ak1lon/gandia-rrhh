<?php

namespace App\Http\Requests\Registro;

use Illuminate\Foundation\Http\FormRequest;

class VerificarDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dni_frente' => ['required', 'file', 'image', 'max:10240'],
            'dni_dorso' => ['required', 'file', 'image', 'max:10240'],
        ];
    }
}

