<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. The apiPrefix is set to
| "api/v1" in bootstrap/app.php.
|
*/

// Public routes
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'api_version' => 'v1',
    ]);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    // Route::post('login', [LoginController::class, 'login']);
    // Route::post('register', [RegisterController::class, 'register']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Account routes
    // Route::apiResource('accounts', AccountsController::class);

    // Account-scoped resources
    // Route::prefix('accounts/{account}')->group(function () {
    //     Route::apiResource('conversations', ConversationsController::class);
    //     Route::apiResource('contacts', ContactsController::class);
    //     Route::apiResource('inboxes', InboxesController::class);
    //     Route::apiResource('teams', TeamsController::class);
    //     Route::apiResource('labels', LabelsController::class);
    //     Route::apiResource('canned_responses', CannedResponsesController::class);
    //     Route::apiResource('automation_rules', AutomationRulesController::class);
    //     Route::apiResource('webhooks', WebhooksController::class);

    //     // Conversation-scoped resources
    //     Route::prefix('conversations/{conversation}')->group(function () {
    //         Route::apiResource('messages', MessagesController::class);
    //         Route::post('assign', [ConversationsController::class, 'assign']);
    //         Route::post('resolve', [ConversationsController::class, 'resolve']);
    //     });
    // });
});
