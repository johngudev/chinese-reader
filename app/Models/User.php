<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserCharactersList;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

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

    /**
     * Get the character list associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function charactersList()
    {
        return $this->hasOne(UserCharactersList::class);
    }

    /**
     * Get the generated texts associated with the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function generatedTexts()
    {
        return $this->hasMany(GeneratedText::class);
    }

    /**
     * Get the saved words associated with the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function savedWords() { return $this->hasMany(SavedWord::class); }

    /**
     * Check if the user has an active premium subscription.
     *
     * @return bool
     */
    public function isPremium(): bool
    {
        return $this->subscribed('default');
    }

}
