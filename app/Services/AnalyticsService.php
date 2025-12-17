<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getPostStats(): array
    {
        // Counts by status
        $byStatus = Post::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Counts by period
        $now = Carbon::now();
        $byPeriod = [
            'day' => Post::where('created_at', '>=', $now->clone()->subDay())->count(),
            'week' => Post::where('created_at', '>=', $now->clone()->subWeek())->count(),
            'month' => Post::where('created_at', '>=', $now->clone()->subMonth())->count(),
        ];

        // Average comments per post
        $totalPosts = Post::count();
        $totalComments = Comment::count();
        $avgComments = $totalPosts > 0 ? round($totalComments / $totalPosts, 2) : 0;

        // Top 5 most commented posts
        $topPosts = Post::withCount('comments')
            ->orderByDesc('comments_count')
            ->take(5)
            ->get()
            ->map(fn(Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'comments_count' => $post->comments_count,
            ]);

        return [
            'by_status' => $byStatus,
            'by_period' => $byPeriod,
            'avg_comments_per_post' => $avgComments,
            'top_commented_posts' => $topPosts,
        ];
    }

    public function getCommentStats(): array
    {
        $total = Comment::count();

        $now = Carbon::now();
        $byPeriod = [
            'day' => Comment::where('created_at', '>=', $now->clone()->subDay())->count(),
            'week' => Comment::where('created_at', '>=', $now->clone()->subWeek())->count(),
            'month' => Comment::where('created_at', '>=', $now->clone()->subMonth())->count(),
        ];

        // Activity by time of day (hour)
        $byHour = Comment::select(DB::raw('extract(hour from created_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->mapWithKeys(fn($item) => [(int)$item->hour => $item->count]);

        // Activity by day of week
        // 1=Mon, 7=Sun
        $byDayOfWeek = Comment::select(DB::raw('extract(isodow from created_at) as dow'), DB::raw('count(*) as count'))
            ->groupBy('dow')
            ->orderBy('dow')
            ->get()
            ->mapWithKeys(fn($item) => [(int)$item->dow => $item->count]);

        return [
            'total_comments' => $total,
            'by_period' => $byPeriod,
            'activity_by_hour' => $byHour,
            'activity_by_dow' => $byDayOfWeek,
        ];
    }

    public function getUserStats(): array
    {
        // Users by role
        $byRole = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        // Top 5 active authors (by post count)
        $topAuthors = User::whereHas('posts')
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'posts_count' => $u->posts_count
            ]);

        // Top 5 active commentators (by comment count)
        $topCommentators = User::whereHas('comments')
            ->withCount('comments')
            ->orderByDesc('comments_count')
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'comments_count' => $u->comments_count
            ]);

        return [
            'by_role' => $byRole,
            'top_authors' => $topAuthors,
            'top_commentators' => $topCommentators,
        ];
    }
}
