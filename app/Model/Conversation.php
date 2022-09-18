<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $casts = [
        'user_id' => 'integer',
        'is_reply' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
      'user_id',
      'message',
      'reply',
      'checked',
      'image',
      'is_reply'
    ];
}
