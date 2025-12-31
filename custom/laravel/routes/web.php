<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This Laravel application is a REST API only project.
| All API routes are defined in routes/api.php.
| This file only provides a basic health check endpoint.
|
*/


// Health check endpoint
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'status' => 'running',
        'documentation' => '/api/documentation',
    ]);
});

// API and Auth endpoints (handled by Laravel controllers)
// These should be defined in routes/api.php and routes/auth.php as needed

// SPA catch-all: Serve SvelteKit index.html for /app and all subroutes
Route::get('/app/{any}', function () {
    return response()->file(public_path('app/index.html'));
})->where('any', '.*');

Route::get('/app', function () {
    return response()->file(public_path('app/index.html'));
});

// Fallback for backend routes (not /app/*)
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
Route::fallback(function () {
    if (request()->is('api/*') || request()->is('auth/*')) {
        return response()->json(['error' => 'Not Found'], 404);
    }
    throw new NotFoundHttpException;
});
