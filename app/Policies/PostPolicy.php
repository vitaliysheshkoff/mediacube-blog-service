<?php

namespace App\Policies;

use App\Enums\TokenAbility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin() || $user->tokenCan(TokenAbility::ALL->value)) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->tokenCan(TokenAbility::POST_CREATE->value);
    }

    public function update(User $user, Post $post): bool
    {
        if (!$user->tokenCan(TokenAbility::POST_UPDATE->value)) {
            return false;
        }

        return $user->id === $post->author_id;
    }

    public function delete(User $user, Post $post): bool
    {
        if (!$user->tokenCan(TokenAbility::POST_DELETE->value)) {
            return false;
        }

        return $user->id === $post->author_id;
    }
}
