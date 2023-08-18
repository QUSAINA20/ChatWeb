<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function messages()
    {
        return $this->hasMany(GroupMessage::class);
    }
}
