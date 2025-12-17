<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MetaController extends Controller
{
    public function roles(): JsonResponse
    {
        return response()->json([
            'data' => array_column(UserRole::cases(), 'value')
        ]);
    }
}
