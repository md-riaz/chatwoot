<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

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

// Broadcasting Auth Routes
// Defined in routes/api.php with correct middleware
// Broadcast::routes(['prefix' => 'api', 'middleware' => ['auth:sanctum']]);

// SAML Authentication Routes
use App\Http\Controllers\Api\V1\Auth\SamlController;
use App\Http\Controllers\Api\V1\Channels\FacebookController;

Route::prefix('saml')->group(function () {
    Route::get('config/{account}', [SamlController::class, 'config'])->name('saml.config');
    Route::get('metadata/{account}', [SamlController::class, 'metadata'])->name('saml.metadata');
    Route::get('login/{account}', [SamlController::class, 'login'])->name('saml.login');
    Route::post('acs/{account}', [SamlController::class, 'acs'])->name('saml.acs');
    Route::get('sls/{account}', [SamlController::class, 'sls'])->name('saml.sls');
    Route::post('sls/{account}', [SamlController::class, 'sls'])->name('saml.sls.post');
});

// SAML token endpoint for SPA
Route::get('auth/saml/token', [SamlController::class, 'token'])->name('saml.token');

Route::get('auth/facebook/callback', [FacebookController::class, 'oauthCallback'])
    ->name('facebook.oauth.callback');
Route::get('auth/instagram/callback', [\App\Http\Controllers\Api\V1\Channels\InstagramController::class, 'oauthCallback'])
    ->name('instagram.oauth.callback');

// SPA catch-all: Serve SvelteKit index.html for /app and all subroutes
Route::get('/app/{any}', function () {
    return response()->file(public_path('app/index.html'));
})->where('any', '.*');

Route::get('/', function () {
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
