<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['chat_id', 'user_id', 'message'];

    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
