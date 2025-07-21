<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Les attributs que l'on peut remplir en masse.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
    ];

    /**
     * Les attributs à cacher lors de la sérialisation.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les casts automatiques.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * Relation 1-1 vers SpotifyAccount.
     */
    public function spotifyAccount(): HasOne
    {
        return $this->hasOne(SpotifyAccount::class);
    }

    /**
     * Relation 1-N vers Recommendation.
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }
}
