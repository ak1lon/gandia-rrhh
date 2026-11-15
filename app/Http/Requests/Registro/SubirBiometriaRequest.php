<?php

namespace App\Http\Requests\Registro;

use Illuminate\Foundation\Http\FormRequest;

class SubirBiometriaRequest extends FormRequest
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
            'foto_biometrica' => ['required', 'file', 'image', 'max:10240'],
        ];
    }
}
