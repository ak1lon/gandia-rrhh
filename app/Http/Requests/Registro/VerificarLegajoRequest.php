<?php

namespace App\Http\Requests\Registro;

use Illuminate\Foundation\Http\FormRequest;

class VerificarLegajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('leg_numero') && $this->filled('legajo_o_dni')) {
            $this->merge(['leg_numero' => $this->input('legajo_o_dni')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'leg_numero' => ['required', 'integer', 'min:1', 'max:99999999'],
            'legajo_o_dni' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function legNumero(): int
    {
        return (int) $this->validated('leg_numero');
    }
}

