<?php

namespace App\Http\Requests\Registro;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DatosPersonalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'genero' => ['nullable', 'string', 'max:10'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'localidad' => ['nullable', 'string', 'max:60'],
            'provincia' => ['nullable', 'string', 'max:60'],
            'telefono' => ['required', 'string', 'max:30'],
            'fecha_nacimiento' => ['nullable', 'date_format:Y-m-d'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
        ];
    }
}

