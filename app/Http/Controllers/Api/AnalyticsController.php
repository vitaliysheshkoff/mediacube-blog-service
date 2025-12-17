<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    const TTL = 600;

    public function __construct(protected AnalyticsService $analyticsService)
    {
    }

    public function posts(): JsonResponse
    {
        return response()->json([
            'data' => Cache::remember('analytics_posts', self::TTL, function () {
                return $this->analyticsService->getPostStats();
            }),
        ]);
    }

    public function comments(): JsonResponse
    {
        return response()->json([
            'data' => Cache::remember('analytics_comments', self::TTL, function () {
                return $this->analyticsService->getCommentStats();
            }),
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json([
            'data' => Cache::remember('analytics_users', self::TTL, function () {
                return $this->analyticsService->getUserStats();
            }),
        ]);
    }
}
