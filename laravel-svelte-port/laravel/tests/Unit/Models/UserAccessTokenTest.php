<?php

use App\Models\AccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User AccessTokenable Integration', function () {
    
    test('User model uses AccessTokenable trait', function () {
        $user = User::factory()->create();
        
        // Verify the user has the accessTokenModel relationship
        expect(method_exists($user, 'accessTokenModel'))->toBeTrue();
        
        // Verify the user has the access_token attribute accessor
        expect(method_exists($user, 'getAccessTokenAttribute'))->toBeTrue();
        
        // Verify the user has the resetAccessToken method
        expect(method_exists($user, 'resetAccessToken'))->toBeTrue();
    });
    
    test('User automatically gets access token on creation', function () {
        $user = User::factory()->create();
        
        // Verify the user has an access token (auto-created by trait)
        expect($user->accessTokenModel)->not->toBeNull();
        expect($user->accessTokenModel)->toBeInstanceOf(AccessToken::class);
        expect($user->access_token)->not->toBeNull();
        expect(strlen($user->access_token))->toBe(64);
    });
    
    test('User access_token attribute returns token string', function () {
        $user = User::factory()->create();
        
        // Verify the access_token attribute returns the token string
        $token = $user->access_token;
        expect($token)->toBeString();
        expect($token)->toBe($user->accessTokenModel->token);
    });
    
    test('User can reset access token', function () {
        $user = User::factory()->create();
        
        $oldToken = $user->access_token;
        
        // Reset the token
        $newToken = $user->resetAccessToken();
        
        // Verify the token changed
        expect($newToken)->not->toBe($oldToken);
        
        // Refresh the model and verify the new token is persisted
        $user->refresh();
        expect($user->access_token)->toBe($newToken);
    });
    
    test('User access token is deleted when user is deleted', function () {
        $user = User::factory()->create();
        $tokenId = $user->accessTokenModel->id;
        
        // Verify token exists
        expect(AccessToken::find($tokenId))->not->toBeNull();
        
        // Delete the user
        $user->delete();
        
        // Verify token is also deleted
        expect(AccessToken::find($tokenId))->toBeNull();
    });
    
    /**
     * Property-based test: For any User model using the AccessTokenable trait,
     * creating a new instance SHALL automatically create an associated AccessToken.
     * 
     * **Validates: Requirements 2.1, 2.4**
     * **Feature: laravel-access-token-parity, Property 4: AccessTokenable Auto-Creates Token**
     */
    test('property test - User auto-creates access token on creation', function () {
        // Test with multiple iterations to simulate property-based testing
        for ($i = 0; $i < 100; $i++) {
            // Create a User (which uses AccessTokenable trait)
            $user = User::factory()->create();
            
            // Verify the user has an access token (auto-created by trait)
            expect($user->accessTokenModel)->not->toBeNull();
            expect($user->accessTokenModel)->toBeInstanceOf(AccessToken::class);
            
            // Verify the access token has the correct owner relationship
            expect($user->accessTokenModel->owner_type)->toBe(User::class);
            expect($user->accessTokenModel->owner_id)->toBe($user->id);
            
            // Verify the access token is a valid 64-character string
            expect($user->access_token)->not->toBeNull();
            expect($user->access_token)->toBeString();
            expect(strlen($user->access_token))->toBe(64);
            
            // Verify the access_token attribute returns the same token as the relationship
            expect($user->access_token)->toBe($user->accessTokenModel->token);
            
            // Verify the token is unique (not empty or default value)
            expect($user->access_token)->not->toBe('');
            expect(ctype_alnum($user->access_token))->toBeTrue();
            
            // Clean up for next iteration
            $user->delete();
        }
    });
});
