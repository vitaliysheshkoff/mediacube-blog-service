<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MetaController extends Controller
{
    public function roles(): JsonResponse
    {
        return response()->json([
            'data' => Cache::tags(['meta'])->rememberForever('meta_roles', function () {
                return array_column(UserRole::cases(), 'value');
            })
        ]);
    }
}
