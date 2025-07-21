<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $fillable = [
        'user_id',
        'data',
        'blacklist_artists',
        'blacklist_genres',
        'blacklist_tracks',
    ];

    protected $casts = [
        'data'              => 'array',
        'blacklist_artists' => 'array',
        'blacklist_genres'  => 'array',
        'blacklist_tracks'  => 'array',
    ];

    /**
     * Relation vers l'utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
