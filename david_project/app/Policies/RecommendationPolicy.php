<?php

namespace App\Policies;

use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecommendationPolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut mettre à jour (toggle) la recommendation.
     */
    public function update(User $user, Recommendation $recommendation): bool
    {
        // Seul le propriétaire de la recommendation peut la modifier
        return $user->id === $recommendation->user_id;
    }
}
