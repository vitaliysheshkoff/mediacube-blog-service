<?php

namespace App\Policies;

use App\Enums\TokenAbility;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin() || $user->tokenCan(TokenAbility::ALL->value)) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Comment $comment): true
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->tokenCan(TokenAbility::COMMENT_CREATE->value);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->tokenCan(TokenAbility::COMMENT_UPDATE->value) && $user->id === $comment->author_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->tokenCan(TokenAbility::COMMENT_DELETE->value) && $user->id === $comment->author_id;
    }
}
