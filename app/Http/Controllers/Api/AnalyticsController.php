<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    public function posts(): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getPostStats(),
        ]);
    }

    public function comments(): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getCommentStats(),
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getUserStats(),
        ]);
    }
}
