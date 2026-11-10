<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registro extends Model
{
    public const ESTADO_EN_CURSO = 'en_curso';

    public const ESTADO_REVISION_PENDIENTE = 'revision_pendiente';

    public const ESTADO_COMPLETADO = 'completado';

    protected $hidden = [
        'didit_payload',
        'dni_frente_path',
        'dni_dorso_path',
    ];

    protected $fillable = [
        'leg_numero',
        'dni',
        'email',
        'genero',
        'domicilio',
        'localidad',
        'provincia',
        'telefono',
        'fecha_nacimiento',
        'dni_frente_path',
        'dni_dorso_path',
        'didit_request_id',
        'didit_status',
        'didit_payload',
        'paso_actual',
        'estado',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'leg_numero' => 'integer',
            'paso_actual' => 'integer',
            'fecha_nacimiento' => 'date',
            'didit_payload' => 'array',
        ];
    }

    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Legajo::class, 'leg_numero', 'leg_numero');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estaEnCurso(): bool
    {
        return in_array($this->estado, [self::ESTADO_EN_CURSO, self::ESTADO_REVISION_PENDIENTE], true);
    }
}

