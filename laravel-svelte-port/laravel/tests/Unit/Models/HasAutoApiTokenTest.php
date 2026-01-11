<?php

use App\Models\AgentBot;
use App\Models\PlatformApp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

describe('HasAutoApiToken Trait (Sanctum Integration)', function () {
    
    describe('Auto Token Creation on Model Creation', function () {
        
        test('User automatically gets Sanctum token on creation', function () {
            $user = User::factory()->create();
            
            // Verify the user has a Sanctum token (auto-created by trait)
            $token = $user->tokens()->where('name', 'api-access')->first();
            expect($token)->not->toBeNull();
            expect($token)->toBeInstanceOf(PersonalAccessToken::class);
            expect($token->tokenable_type)->toBe(User::class);
            expect($token->tokenable_id)->toBe($user->id);
        });
        
        test('AgentBot automatically gets Sanctum token on creation', function () {
            $bot = AgentBot::factory()->create();
            
            // Verify the bot has a Sanctum token (auto-created by trait)
            $token = $bot->tokens()->where('name', 'api-access')->first();
            expect($token)->not->toBeNull();
            expect($token)->toBeInstanceOf(PersonalAccessToken::class);
            expect($token->tokenable_type)->toBe(AgentBot::class);
            expect($token->tokenable_id)->toBe($bot->id);
        });
        
        test('PlatformApp automatically gets Sanctum token on creation', function () {
            $app = PlatformApp::factory()->create();
            
            // Verify the platform app has a Sanctum token (auto-created by trait)
            $token = $app->tokens()->where('name', 'api-access')->first();
            expect($token)->not->toBeNull();
            expect($token)->toBeInstanceOf(PersonalAccessToken::class);
            expect($token->tokenable_type)->toBe(PlatformApp::class);
            expect($token->tokenable_id)->toBe($app->id);
        });
        
        /**
         * Property-based test: For any model using HasAutoApiToken trait,
         * creating a new instance SHALL automatically create an associated Sanctum token.
         */
        test('property test - all models with HasAutoApiToken auto-create Sanctum tokens', function () {
            for ($i = 0; $i < 10; $i++) {
                $models = [
                    ['model' => User::factory()->create(), 'type' => User::class],
                    ['model' => AgentBot::factory()->create(), 'type' => AgentBot::class],
                    ['model' => PlatformApp::factory()->create(), 'type' => PlatformApp::class],
                ];
                
                foreach ($models as $modelData) {
                    $model = $modelData['model'];
                    $expectedType = $modelData['type'];
                    
                    // Verify the model has a Sanctum token
                    $token = $model->tokens()->where('name', 'api-access')->first();
                    expect($token)->not->toBeNull();
                    expect($token)->toBeInstanceOf(PersonalAccessToken::class);
                    expect($token->tokenable_type)->toBe($expectedType);
                    expect($token->tokenable_id)->toBe($model->id);
                    expect($token->name)->toBe('api-access');
                    
                    // Clean up
                    $model->tokens()->delete();
                    $model->delete();
                }
            }
        });
    });
    
    describe('Token Reset Functionality', function () {
        
        test('User can reset access token', function () {
            $user = User::factory()->create();
            
            $oldTokenId = $user->tokens()->where('name', 'api-access')->first()->id;
            
            // Reset the token
            $newToken = $user->resetAccessToken();
            
            // Verify we got a new plain text token
            expect($newToken)->toBeString();
            expect($newToken)->not->toBeEmpty();
            
            // Verify old token is deleted and new one exists
            expect(PersonalAccessToken::find($oldTokenId))->toBeNull();
            
            $newTokenModel = $user->tokens()->where('name', 'api-access')->first();
            expect($newTokenModel)->not->toBeNull();
            expect($newTokenModel->id)->not->toBe($oldTokenId);
        });
        
        test('AgentBot can reset access token', function () {
            $bot = AgentBot::factory()->create();
            
            $oldTokenId = $bot->tokens()->where('name', 'api-access')->first()->id;
            
            // Reset the token
            $newToken = $bot->resetAccessToken();
            
            // Verify we got a new plain text token
            expect($newToken)->toBeString();
            expect($newToken)->not->toBeEmpty();
            
            // Verify old token is deleted and new one exists
            expect(PersonalAccessToken::find($oldTokenId))->toBeNull();
            
            $newTokenModel = $bot->tokens()->where('name', 'api-access')->first();
            expect($newTokenModel)->not->toBeNull();
        });
        
        test('PlatformApp can reset access token', function () {
            $app = PlatformApp::factory()->create();
            
            $oldTokenId = $app->tokens()->where('name', 'api-access')->first()->id;
            
            // Reset the token
            $newToken = $app->resetAccessToken();
            
            // Verify we got a new plain text token
            expect($newToken)->toBeString();
            expect($newToken)->not->toBeEmpty();
            
            // Verify old token is deleted and new one exists
            expect(PersonalAccessToken::find($oldTokenId))->toBeNull();
            
            $newTokenModel = $app->tokens()->where('name', 'api-access')->first();
            expect($newTokenModel)->not->toBeNull();
        });
    });
    
    describe('Token Deletion on Model Deletion', function () {
        
        test('deleting User deletes associated Sanctum tokens', function () {
            $user = User::factory()->create();
            $tokenId = $user->tokens()->where('name', 'api-access')->first()->id;
            
            // Verify token exists
            expect(PersonalAccessToken::find($tokenId))->not->toBeNull();
            
            // Delete the user
            $user->delete();
            
            // Verify token is also deleted (Sanctum handles this via foreign key or model events)
            expect(PersonalAccessToken::find($tokenId))->toBeNull();
        });
        
        test('deleting AgentBot deletes associated Sanctum tokens', function () {
            $bot = AgentBot::factory()->create();
            $tokenId = $bot->tokens()->where('name', 'api-access')->first()->id;
            
            // Verify token exists
            expect(PersonalAccessToken::find($tokenId))->not->toBeNull();
            
            // Delete the bot
            $bot->delete();
            
            // Verify token is also deleted
            expect(PersonalAccessToken::find($tokenId))->toBeNull();
        });
        
        test('deleting PlatformApp deletes associated Sanctum tokens', function () {
            $app = PlatformApp::factory()->create();
            $tokenId = $app->tokens()->where('name', 'api-access')->first()->id;
            
            // Verify token exists
            expect(PersonalAccessToken::find($tokenId))->not->toBeNull();
            
            // Delete the platform app
            $app->delete();
            
            // Verify token is also deleted
            expect(PersonalAccessToken::find($tokenId))->toBeNull();
        });
        
        /**
         * Property 5: Dependent Destroy Cascades
         * 
         * For any model using the HasAutoApiToken trait (User, AgentBot, PlatformApp),
         * deleting the model SHALL also delete its associated Sanctum tokens.
         * 
         * **Validates: Requirements 2.2**
         */
        test('property test - cascade delete removes all tokens for any model with HasAutoApiToken', function () {
            // Run 100 iterations as per property-based testing requirements
            for ($i = 0; $i < 100; $i++) {
                // Randomly select a model type to test
                $modelType = fake()->randomElement(['User', 'AgentBot', 'PlatformApp']);
                
                // Create the model based on type
                $model = match ($modelType) {
                    'User' => User::factory()->create(),
                    'AgentBot' => AgentBot::factory()->create(),
                    'PlatformApp' => PlatformApp::factory()->create(),
                };
                
                // Get all token IDs before deletion
                $tokenIds = $model->tokens()->pluck('id')->toArray();
                
                // Verify at least one token exists (auto-created by trait)
                expect($tokenIds)->not->toBeEmpty(
                    "Model {$modelType} should have at least one token before deletion"
                );
                
                // Optionally add additional tokens to test multiple token deletion
                if (fake()->boolean(30)) {
                    $model->createToken('additional-token-' . fake()->uuid());
                    $tokenIds = $model->tokens()->pluck('id')->toArray();
                }
                
                // Delete the model
                $model->delete();
                
                // Verify ALL tokens are deleted (cascade delete)
                foreach ($tokenIds as $tokenId) {
                    expect(PersonalAccessToken::find($tokenId))->toBeNull(
                        "Token {$tokenId} should be deleted when {$modelType} is deleted"
                    );
                }
                
                // Also verify no orphaned tokens exist for this model
                $orphanedTokens = PersonalAccessToken::where('tokenable_type', get_class($model))
                    ->where('tokenable_id', $model->id)
                    ->count();
                    
                expect($orphanedTokens)->toBe(0,
                    "No orphaned tokens should exist for deleted {$modelType}"
                );
            }
        });
    });
    
    describe('Helper Methods', function () {
        
        test('getApiTokenModel returns the Sanctum token model', function () {
            $user = User::factory()->create();
            
            $tokenModel = $user->getApiTokenModel();
            
            expect($tokenModel)->not->toBeNull();
            expect($tokenModel)->toBeInstanceOf(PersonalAccessToken::class);
            expect($tokenModel->name)->toBe('api-access');
        });
        
        test('ensureApiToken returns null if token already exists', function () {
            $user = User::factory()->create();
            
            // Token already exists from creation
            $result = $user->ensureApiToken();
            
            expect($result)->toBeNull();
        });
        
        test('ensureApiToken creates token if none exists', function () {
            $user = User::factory()->create();
            
            // Delete the auto-created token
            $user->tokens()->delete();
            
            // Now ensureApiToken should create one
            $result = $user->ensureApiToken();
            
            expect($result)->not->toBeNull();
            expect($result->plainTextToken)->toBeString();
            expect($user->tokens()->where('name', 'api-access')->exists())->toBeTrue();
        });
    });
});
