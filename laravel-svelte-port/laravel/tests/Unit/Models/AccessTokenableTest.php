<?php

use App\Models\AccessToken;
use App\Models\AgentBot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('AccessTokenable Trait', function () {
    
    describe('Property 5: Dependent Destroy Cascades', function () {
        
        test('deleting AgentBot cascades to delete associated AccessToken', function () {
            // Create an AgentBot (which uses AccessTokenable trait)
            $bot = AgentBot::factory()->create();
            
            // Verify the bot has an access token (auto-created by trait)
            expect($bot->accessTokenModel)->not->toBeNull();
            $tokenId = $bot->accessTokenModel->id;
            
            // Verify the token exists in database
            expect(AccessToken::find($tokenId))->not->toBeNull();
            
            // Delete the bot
            $bot->delete();
            
            // Verify the associated access token is also deleted (cascade delete)
            expect(AccessToken::find($tokenId))->toBeNull();
        });
        
        test('deleting User cascades to delete associated AccessToken', function () {
            // Create a User (which now uses AccessTokenable trait)
            $user = User::factory()->create();
            
            // Verify the user has an access token (auto-created by trait)
            expect($user->accessTokenModel)->not->toBeNull();
            $tokenId = $user->accessTokenModel->id;
            
            // Verify the token exists in database
            expect(AccessToken::find($tokenId))->not->toBeNull();
            
            // Delete the user
            $user->delete();
            
            // Verify the associated access token is also deleted (cascade delete)
            expect(AccessToken::find($tokenId))->toBeNull();
        });
        
        /**
         * Property-based test: For any model using AccessTokenable trait,
         * deleting the model SHALL also delete its associated AccessToken.
         * 
         * **Validates: Requirements 2.2**
         * **Feature: laravel-access-token-parity, Property 5: Dependent Destroy Cascades**
         */
        test('property test - cascade delete works for any AccessTokenable model', function () {
            // Test with multiple iterations to simulate property-based testing
            for ($i = 0; $i < 10; $i++) {
                // Create different types of models that use AccessTokenable
                $models = [
                    AgentBot::factory()->create(),
                    User::factory()->create(),
                ];
                
                foreach ($models as $model) {
                    // Verify the model has an access token (auto-created by trait)
                    expect($model->accessTokenModel)->not->toBeNull();
                    $tokenId = $model->accessTokenModel->id;
                    
                    // Verify the token exists in database
                    expect(AccessToken::find($tokenId))->not->toBeNull();
                    
                    // Delete the model
                    $model->delete();
                    
                    // Verify the associated access token is also deleted (cascade delete)
                    expect(AccessToken::find($tokenId))->toBeNull();
                }
            }
        });
        
        test('cascade delete only affects the specific model\'s token', function () {
            // Create two bots
            $bot1 = AgentBot::factory()->create();
            $bot2 = AgentBot::factory()->create();
            
            // Get their token IDs
            $token1Id = $bot1->accessTokenModel->id;
            $token2Id = $bot2->accessTokenModel->id;
            
            // Verify both tokens exist
            expect(AccessToken::find($token1Id))->not->toBeNull();
            expect(AccessToken::find($token2Id))->not->toBeNull();
            
            // Delete only the first bot
            $bot1->delete();
            
            // Verify only the first bot's token is deleted
            expect(AccessToken::find($token1Id))->toBeNull();
            expect(AccessToken::find($token2Id))->not->toBeNull();
        });
        
        test('cascade delete works when model has no access token', function () {
            // Create a bot
            $bot = AgentBot::factory()->create();
            
            // Manually delete its access token first
            $bot->accessTokenModel->delete();
            
            // Deleting the bot should not cause errors even without access token
            $bot->delete();
            
            // If we get here without exception, the test passes
            expect(true)->toBeTrue();
        });
    });
});