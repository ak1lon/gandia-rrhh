<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = ['title', 'body', 'recipients', 'status', 'firebase_responses'];

    protected $casts = [
        'recipients' => 'array',
        'firebase_responses' => 'array',
    ];
}
