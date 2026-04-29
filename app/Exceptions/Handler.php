<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'message' => 'No autenticado'
        ], 401);
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => 'Errores de validación',
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
                'type' => class_basename($e)
            ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        }

        return parent::render($request, $e);
    }
}
