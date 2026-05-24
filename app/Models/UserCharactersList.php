<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCharactersList extends Model
{
    use HasFactory;

    protected $table = 'user_characters_lists';
    protected $fillable = ['user_id', 'characters_list'];
    protected $casts = ['characters_list' => 'array'];
}
