<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedText extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'prompt', 'generated_text'];

    /**
     * Get the user that owns the generated text.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
