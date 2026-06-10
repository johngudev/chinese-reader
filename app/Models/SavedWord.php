<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedWord extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'generated_text_id', 'word', 'pinyin', 'english'];

}
