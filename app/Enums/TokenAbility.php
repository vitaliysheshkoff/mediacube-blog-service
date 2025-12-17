<?php

namespace App\Enums;

enum TokenAbility: string
{
    case POST_CREATE = 'post:create';
    case POST_UPDATE = 'post:update';
    case POST_DELETE = 'post:delete';
    case POST_VIEW = 'post:view';

    case COMMENT_CREATE = 'comment:create';
    case COMMENT_UPDATE = 'comment:update';
    case COMMENT_DELETE = 'comment:delete';
    case COMMENT_VIEW = 'comment:view';

    case ALL = '*';
}
