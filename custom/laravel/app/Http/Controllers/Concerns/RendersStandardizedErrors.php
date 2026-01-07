<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

trait RendersStandardizedErrors
{
    /**
     * Render a simple error response (matches Rails format).
     */
    protected function renderError(string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }

    /**
     * Render unauthorized error (matches Rails format).
     */
    protected function renderUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Render not found error (matches Rails format).
     */
    protected function renderNotFound(string $message = 'Resource not found'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_NOT_FOUND);
    }

    /**
     * Render unprocessable entity error (matches Rails format).
     */
    protected function renderUnprocessableEntity(string $message = 'Unprocessable entity'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Render validation errors (matches Rails ActiveRecord format).
     */
    protected function renderValidationErrors($errors, string $message = null): JsonResponse
    {
        if ($errors instanceof ValidationException) {
            $errorMessages = $errors->errors();
            $attributes = array_keys($errorMessages);
            
            // Flatten error messages like Rails does
            $flattenedMessages = [];
            foreach ($errorMessages as $field => $messages) {
                foreach ($messages as $msg) {
                    $flattenedMessages[] = $msg;
                }
            }
            
            return response()->json([
                'message' => $message ?? implode(', ', $flattenedMessages),
                'attributes' => $attributes,
                'error' => $errorMessages, // Include detailed errors like Rails
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle Eloquent model validation errors
        if (is_object($errors) && method_exists($errors, 'getMessageBag')) {
            $errorMessages = $errors->getMessageBag()->toArray();
            $attributes = array_keys($errorMessages);
            
            $flattenedMessages = [];
            foreach ($errorMessages as $field => $messages) {
                foreach ($messages as $msg) {
                    $flattenedMessages[] = $msg;
                }
            }
            
            return response()->json([
                'message' => $message ?? implode(', ', $flattenedMessages),
                'attributes' => $attributes,
                'error' => $errorMessages,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle array of errors
        if (is_array($errors)) {
            return response()->json([
                'error' => $errors,
                'message' => $message ?? 'Validation failed',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Fallback for string errors
        return response()->json([
            'error' => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Render internal server error (matches Rails format).
     */
    protected function renderInternalServerError(string $message = 'Internal server error'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Render payment required error (matches Rails format).
     */
    protected function renderPaymentRequired(string $message = 'Payment required'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_PAYMENT_REQUIRED);
    }

    /**
     * Render forbidden error (matches Rails format).
     */
    protected function renderForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_FORBIDDEN);
    }

    /**
     * Render method not allowed error (matches Rails format).
     */
    protected function renderMethodNotAllowed(string $message = 'Method not allowed'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Render conflict error (matches Rails format).
     */
    protected function renderConflict(string $message = 'Conflict'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_CONFLICT);
    }

    /**
     * Render gone error (matches Rails format).
     */
    protected function renderGone(string $message = 'Resource no longer available'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_GONE);
    }

    /**
     * Render too many requests error (matches Rails format).
     */
    protected function renderTooManyRequests(string $message = 'Too many requests'): JsonResponse
    {
        return response()->json(['error' => $message], Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Handle common exceptions and render appropriate error responses.
     */
    protected function handleException(\Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return $this->renderValidationErrors($exception);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->renderNotFound('Resource not found');
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->renderUnauthorized('Authentication required');
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->renderForbidden('Access denied');
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return $this->renderNotFound('Endpoint not found');
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return $this->renderMethodNotAllowed('HTTP method not allowed');
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
            return $this->renderTooManyRequests('Rate limit exceeded');
        }

        // Log unexpected errors
        \Log::error('Unexpected error in API controller', [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return $this->renderInternalServerError('An unexpected error occurred');
    }
}