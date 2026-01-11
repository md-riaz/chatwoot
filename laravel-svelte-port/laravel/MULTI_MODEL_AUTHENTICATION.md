# Multi-Model Access Token Authentication

## Overview

This document explains the Laravel implementation of multi-model access token authentication that achieves functional parity with the Rails backend while following Laravel best practices.

## Problem Statement

The original issue was that User, AgentBot, and PlatformApp access tokens were not enforcing access control correctly. The root cause was that Laravel Sanctum's default guard only authenticates User models, leaving AgentBot and PlatformApp instances unable to authenticate even though they had valid tokens.

## Solution

We implemented a **custom Sanctum guard** (`MultiModelSanctumGuard`) that extends Laravel Sanctum to support authentication of multiple model types from the same `personal_access_tokens` table.

### Key Components

#### 1. MultiModelSanctumGuard (`app/Auth/MultiModelSanctumGuard.php`)

Extends `Laravel\Sanctum\Guard` to:
- Check the `tokenable_type` column in `personal_access_tokens`
- Instantiate the correct model (User, AgentBot, or PlatformApp)
- Support both Bearer token and custom `api_access_token` header
- Maintain all Sanctum security features (token hashing, expiration, etc.)

**How it works:**
```php
// 1. Extract token from request (Bearer or api_access_token header)
$token = $this->getTokenFromRequest();

// 2. Look up token in personal_access_tokens table
$accessToken = Sanctum::$personalAccessTokenModel::findToken($token);

// 3. Get the tokenable (polymorphic relationship)
$tokenable = $accessToken->tokenable;  // Can be User, AgentBot, or PlatformApp

// 4. Verify it's an allowed model and return
return $this->user = $tokenable->withAccessToken($accessToken);
```

#### 2. MultiModelAuthServiceProvider (`app/Providers/MultiModelAuthServiceProvider.php`)

Registers the custom guard as the 'sanctum' auth driver:
```php
Auth::extend('sanctum', function ($app, $name, array $config) {
    return new MultiModelSanctumGuard(...);
});
```

#### 3. Updated Middleware

**PlatformAppAuthentication** (`app/Http/Middleware/PlatformAppAuthentication.php`):
- Explicitly authenticates using `Auth::guard('sanctum')`
- Validates authenticated entity is a PlatformApp
- Sets request attributes for downstream middleware

**ValidateBotAccess** (unchanged):
- Already checks if `Auth::user()` is AgentBot
- Now works because guard returns AgentBot instances

**ValidatePlatformPermissible** (unchanged):
- Already checks platform app permissions
- Now works because guard returns PlatformApp instances

## Database Schema

The solution uses Sanctum's existing `personal_access_tokens` table:

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255),  -- 'App\Models\User', 'App\Models\AgentBot', 'App\Models\PlatformApp'
    tokenable_id BIGINT,           -- ID of the user/bot/platform
    name VARCHAR(255),             -- Token name (e.g., 'api-access')
    token VARCHAR(64) UNIQUE,      -- Hashed token
    abilities TEXT,                -- Token permissions
    last_used_at TIMESTAMP,        -- Track usage
    expires_at TIMESTAMP,          -- Token expiration
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

No new tables needed! The polymorphic `tokenable_type` / `tokenable_id` columns already support multiple models.

## Authentication Flow

### User Authentication (Backward Compatible)

```
User Token Request → MultiModelSanctumGuard → tokenable_type = 'App\Models\User'
                                            → Instantiate User
                                            → ValidateBotAccess (skipped for Users)
                                            → Controller receives User
```

### AgentBot Authentication

```
Bot Token Request → MultiModelSanctumGuard → tokenable_type = 'App\Models\AgentBot'
                                           → Instantiate AgentBot
                                           → ValidateBotAccess (checks endpoint whitelist)
                                           → Controller receives AgentBot
```

### PlatformApp Authentication

```
Platform Token Request → PlatformAppAuthentication → Auth::guard('sanctum')->user()
                                                   → tokenable_type = 'App\Models\PlatformApp'
                                                   → Instantiate PlatformApp
                                                   → ValidatePlatformPermissible (checks permissions)
                                                   → Controller receives PlatformApp
```

## Usage Examples

### User API Access
```bash
# Bearer token (standard Sanctum)
curl -H "Authorization: Bearer {token}" http://localhost:8000/api/v1/auth/me

# Custom header (Rails compatibility)
curl -H "api_access_token: {token}" http://localhost:8000/api/v1/auth/me
```

### AgentBot API Access
```bash
# Bot can access whitelisted endpoints
curl -H "Authorization: Bearer {bot_token}" \
  -X POST http://localhost:8000/api/v1/accounts/1/conversations

# Bot cannot access restricted endpoints
curl -H "Authorization: Bearer {bot_token}" \
  http://localhost:8000/api/v1/accounts/1/users
# Returns: 401 "Access to this endpoint is not authorized for bots"
```

### PlatformApp API Access
```bash
# Platform app can access platform routes
curl -H "Authorization: Bearer {platform_token}" \
  http://localhost:8000/api/v1/platform/agent_bots

# Platform app can only access permissible resources
curl -H "Authorization: Bearer {platform_token}" \
  http://localhost:8000/api/v1/platform/agent_bots/123
# Returns: 401 "Non permissible resource" if bot not in permissibles
```

## Token Management

### Automatic Token Creation

Models using the `HasAutoApiToken` trait automatically get tokens:

```php
// User created → token auto-created
$user = User::factory()->create();
$token = $user->tokens()->where('name', 'api-access')->first();
echo $token->token;  // Use this for authentication

// Same for AgentBot and PlatformApp
$bot = AgentBot::factory()->create();
$platformApp = PlatformApp::factory()->create();
```

### Resetting Tokens

```php
// User resets their token
$user->resetAccessToken();

// Admin resets bot token
$bot->resetAccessToken();

// Platform app regenerates token
$platformApp->resetAccessToken();
```

## Rails Parity

### Achieved Parity ✅

1. **Polymorphic Access Tokens**: Single table for all entity types
2. **Custom Header Support**: `api_access_token` and `HTTP_API_ACCESS_TOKEN` headers
3. **Bot Endpoint Restrictions**: Whitelist of allowed bot endpoints
4. **Platform Resource Permissions**: `platform_app_permissibles` validation
5. **Automatic Token Creation**: Tokens created on model creation
6. **Token Reset**: Reset endpoints for users and bots

### Implementation Differences

| Rails | Laravel | Rationale |
|-------|---------|-----------|
| `access_tokens` table with polymorphic owner | `personal_access_tokens` table with polymorphic tokenable | Laravel standard - uses Sanctum's existing table |
| `AccessTokenable` concern | `HasAutoApiToken` trait | Laravel naming convention |
| `has_secure_token :token` | Sanctum's token hashing | Laravel standard - more secure (hashed tokens) |
| `Current.user` | `Auth::user()` | Laravel standard - uses Auth facade |
| Multiple middleware in concerns | Dedicated middleware classes | Laravel standard - separation of concerns |

## Security Considerations

### Token Security

1. **Tokens are hashed**: Sanctum hashes tokens before storage (more secure than Rails)
2. **Token abilities**: Sanctum supports granular permissions per token
3. **Token expiration**: Configurable token expiration via `config/sanctum.php`
4. **Last used tracking**: Automatic tracking of token usage for audit

### Access Control

1. **Bot Restrictions**: Bots can only access whitelisted endpoints
2. **Platform Permissions**: Platform apps require explicit resource permissions
3. **Type Checking**: Guard validates model type before authentication
4. **Request Validation**: Middleware validates permissions before controller access

## Testing

Run the comprehensive test suite:

```bash
# All multi-model auth tests
php artisan test tests/Feature/Auth/MultiModelAuthenticationTest.php

# Specific test
php artisan test --filter test_agent_bot_can_authenticate_with_sanctum_token

# With coverage
php artisan test tests/Feature/Auth/MultiModelAuthenticationTest.php --coverage
```

Test coverage includes:
- User authentication (backward compatibility)
- AgentBot authentication and access control
- PlatformApp authentication and resource permissions
- Both Bearer token and custom headers
- Invalid tokens and unauthorized access

## Troubleshooting

### Issue: Platform app can't authenticate

**Symptoms**: 401 "Invalid access_token" on platform routes

**Solution**: Verify the platform app has a token:
```php
$platformApp = PlatformApp::find($id);
$token = $platformApp->tokens()->where('name', 'api-access')->first();
```

### Issue: Bot is blocked from allowed endpoint

**Symptoms**: 401 "Access to this endpoint is not authorized for bots"

**Solution**: Check the endpoint is in the bot whitelist:
```php
// Add to BOT_ACCESSIBLE_ENDPOINTS in ValidateBotAccess middleware
public const BOT_ACCESSIBLE_ENDPOINTS = [
    'api/v1/accounts/conversations' => ['store', 'update', ...],
];
```

### Issue: Platform app can't access resource

**Symptoms**: 401 "Non permissible resource"

**Solution**: Add resource to platform app permissibles:
```php
$platformApp->permissibles()->create([
    'permissible_type' => AgentBot::class,
    'permissible_id' => $bot->id,
]);
```

## Future Enhancements

Potential improvements:
1. **Token Scopes**: Add ability-based permissions per token
2. **Token Rotation**: Automatic token rotation on security events
3. **Audit Logging**: Detailed logging of token usage
4. **Rate Limiting**: Token-specific rate limits
5. **Token Revocation**: Mass token revocation by model type

## References

- [Laravel Sanctum Documentation](https://laravel.com/docs/11.x/sanctum)
- [Custom Guards in Laravel](https://laravel.com/docs/11.x/authentication#adding-custom-guards)
- [Rails AccessTokenable Concern](../../app/models/concerns/access_tokenable.rb)
- [Specification Documents](.kiro/specs/laravel-access-token-parity/)
