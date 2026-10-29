<?php

namespace App\Http\Requests\Registro;

class IniciarRegistroRequest extends VerificarLegajoRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'es_alta_nueva' => ['sometimes', 'boolean'],
            'dni' => ['required', 'string', 'max:15'],
        ];
    }
}

