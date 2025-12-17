<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e): SymfonyResponse|JsonResponse
    {
        if ($this->isApiRequest($request)) {
            return $this->getErrorResponseForApi($e);
        }

        return parent::render($request, $e);
    }

    /**
     * @param Throwable $e
     * @return JsonResponse|SymfonyResponse
     */
    protected function getErrorResponseForApi(Throwable $e): SymfonyResponse|JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->jsonResponse(
                ['messages' => [$e->validator->getMessageBag()->toArray()]],
                SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->jsonResponse(
                ['message' => 'Resource not found.'],
                SymfonyResponse::HTTP_NOT_FOUND
            );
        }

        $code = match (true) {
            $e instanceof AuthenticationException => SymfonyResponse::HTTP_UNAUTHORIZED,
            $e instanceof AuthorizationException => SymfonyResponse::HTTP_FORBIDDEN,
            $e instanceof ThrottleRequestsException => SymfonyResponse::HTTP_TOO_MANY_REQUESTS,
            default => $e->getCode(),
        };

        $message = app()->environment('production') ? 'Unhandled error' : $e->getMessage();

        $httpCode = ($code >= 300 && $code <= 599) ? $code : 500;

        return $this->jsonResponse(['message' => $message], $httpCode);
    }

    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*');
    }

    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $this->isApiRequest($request) || parent::shouldReturnJson($request, $e);
    }

    protected function jsonResponse(array $payload = null, int $statusCode = 404): SymfonyResponse|JsonResponse
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }
}
