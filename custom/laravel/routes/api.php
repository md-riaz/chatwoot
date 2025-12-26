<?php

use App\Http\Controllers\Api\V1\AccountsController;
use App\Http\Controllers\Api\V1\ContactsController;
use App\Http\Controllers\Api\V1\ConversationsController;
use App\Http\Controllers\Api\V1\InboxesController;
use App\Http\Controllers\Api\V1\MessagesController;
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

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Account routes
    Route::apiResource('accounts', AccountsController::class);

    // Account-scoped resources
    Route::prefix('accounts/{account}')->group(function () {
        // Conversations
        Route::apiResource('conversations', ConversationsController::class);
        Route::post('conversations/{conversation}/assign', [ConversationsController::class, 'assign']);
        Route::post('conversations/{conversation}/resolve', [ConversationsController::class, 'resolve']);

        // Messages (nested under conversations)
        Route::apiResource('conversations/{conversation}/messages', MessagesController::class);

        // Contacts
        Route::apiResource('contacts', ContactsController::class);
        Route::post('contacts/{contact}/merge', [ContactsController::class, 'merge']);

        // Inboxes
        Route::apiResource('inboxes', InboxesController::class);
        Route::get('inboxes/{inbox}/members', [InboxesController::class, 'members']);
        Route::post('inboxes/{inbox}/members', [InboxesController::class, 'addMember']);
        Route::delete('inboxes/{inbox}/members', [InboxesController::class, 'removeMember']);
    });
});

