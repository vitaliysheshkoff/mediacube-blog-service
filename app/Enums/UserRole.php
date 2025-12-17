<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';

    public function abilities(): array
    {
        return match ($this) {
            self::ADMIN => [TokenAbility::ALL->value],
            self::EDITOR => [
                TokenAbility::POST_CREATE->value,
                TokenAbility::POST_UPDATE->value,
                TokenAbility::POST_DELETE->value,
                TokenAbility::COMMENT_CREATE->value,
                TokenAbility::COMMENT_UPDATE->value,
                TokenAbility::COMMENT_DELETE->value,
            ],
            self::VIEWER => [
                TokenAbility::POST_VIEW->value,
                TokenAbility::COMMENT_VIEW->value
            ],
        };
    }
}
