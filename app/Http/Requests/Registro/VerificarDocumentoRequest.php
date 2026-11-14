<?php

namespace App\Http\Requests\Registro;

use Illuminate\Foundation\Http\FormRequest;

class VerificarDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach ($this->allFiles() as $key => $file) {
            $trimmed = trim((string) $key, " \t\n\r\0\x0B_");
            if ($trimmed !== $key && ! $this->hasFile($trimmed)) {
                $this->files->set($trimmed, $file);
            }
        }
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

