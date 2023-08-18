<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }
    public function groupMessages()
    {
        return $this->hasMany(GroupMessage::class);
    }
    public function createdGroups()
    {
        return $this->hasMany(Group::class, 'creator_id');
    }
    public function adminGroups()
    {
        return $this->hasMany(Group::class, 'admin_id');
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
