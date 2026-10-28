<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'sender',
        'sender_show_section',
        'reason',
        'priority',
        'notification_date',
        'image_path',
        'recipients',
        'status',
        'firebase_responses',
    ];

    protected $casts = [
        'sender_show_section' => 'boolean',
        'notification_date' => 'date',
        'recipients' => 'array',
        'firebase_responses' => 'array',
    ];
}
