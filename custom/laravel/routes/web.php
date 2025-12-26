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

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'status' => 'running',
        'documentation' => '/api/documentation',
    ]);
});
