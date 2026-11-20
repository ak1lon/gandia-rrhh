<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    public const STATUS_PROGRAMADA = 'Programada';
    public const STATUS_PROCESANDO = 'Procesando';
    public const STATUS_ENVIADO = 'Enviado correctamente';
    public const STATUS_ENVIADO_ERRORES = 'Enviado con errores';
    public const STATUS_SIN_DISPOSITIVOS = 'Sin dispositivos registrados';
    public const STATUS_CANCELADA = 'Cancelada';

    protected $fillable = [
        'title',
        'body',
        'sender',
        'sender_show_section',
        'reason',
        'priority',
        'notification_date',
        'scheduled_at',
        'sent_at',
        'cancelled_at',
        'image_path',
        'link_url',
        'action_text',
        'recipients',
        'status',
        'firebase_responses',
    ];

    protected $casts = [
        'sender_show_section' => 'boolean',
        'notification_date' => 'date',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'recipients' => 'array',
        'firebase_responses' => 'array',
    ];

    public function estaProgramada(): bool
    {
        return $this->status === self::STATUS_PROGRAMADA;
    }

    public function scopePendientesDeEnvio(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PROGRAMADA)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }
}
