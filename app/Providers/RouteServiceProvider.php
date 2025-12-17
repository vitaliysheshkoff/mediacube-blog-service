<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            $maxAttempts = $request->isMethod('GET') ? 60 : 20;

            return Limit::perMinute($maxAttempts)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn(Request $request, array $headers) => throw new TooManyRequestsHttpException(
                    message: 'Too many requests. Please try again later.',
                    code: SymfonyResponse::HTTP_TOO_MANY_REQUESTS,
                    headers: $headers
                ));
        });

        RateLimiter::for('api.auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(fn(Request $request, array $headers) => throw new TooManyRequestsHttpException(
                    message: 'Too many login attempts. Please try again in a minute.',
                    code: SymfonyResponse::HTTP_TOO_MANY_REQUESTS,
                    headers: $headers
                ));
        });
    }
}
