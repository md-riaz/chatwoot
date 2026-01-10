# Design Document: Laravel Access Token Parity

## Overview

This design document describes the implementation of a polymorphic access token system in Laravel that achieves functional parity with the Rails backend. The implementation uses Laravel best practices including Eloquent traits, middleware, and Laravel's built-in authentication features.

**Current State Analysis:**
- ✅ `AccessToken` model exists with polymorphic relationship and auto-generation
- ✅ `AccessTokenable` trait exists with auto-creation on model creation
- ✅ `AgentBot` model uses `AccessTokenable` trait
- ⚠️ `User` model does NOT use `AccessTokenable` trait (uses Sanctum tokens instead)
- ⚠️ `PlatformApp` model does NOT use `AccessTokenable` trait (has inline token generation)
- ❌ No `AccessTokenAuthentication` middleware for `api_access_token` header
- ❌ No `ValidateBotAccess` middleware for bot endpoint restrictions
- ❌ No `PlatformAppAuthentication` middleware
- ⚠️ Profile `resetAccessToken` uses Sanctum tokens, not polymorphic AccessToken
- ⚠️ SuperAdmin AccessTokensController manages Sanctum tokens, not polymorphic AccessToken

**Key Decision:** The Rails system uses a single polymorphic `AccessToken` model for all entities (User, AgentBot, PlatformApp). The current Laravel implementation mixes Sanctum tokens (for Users) with polymorphic AccessToken (for AgentBot). To achieve functional parity, we need to:
1. Add `AccessTokenable` trait to User and PlatformApp models
2. Create middleware for `api_access_token` header authentication
3. Update controllers to use polymorphic AccessToken instead of Sanctum for API access token auth

## Architecture

The access token system follows Laravel's layered architecture:

```
┌─────────────────────────────────────────────────────────────────┐
│                        HTTP Request                              │
│                   (api_access_token header)                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                 AccessTokenAuthentication Middleware             │
│  - Checks for api_access_token header                           │
│  - Looks up AccessToken by token value                          │
│  - Sets authenticated user/resource                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                 ValidateBotAccess Middleware                     │
│  - Checks if owner is AgentBot                                  │
│  - Validates endpoint is in BOT_ACCESSIBLE_ENDPOINTS            │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Controller                                │
│  - Accesses authenticated resource via request                  │
│  - Performs business logic                                      │
└─────────────────────────────────────────────────────────────────┘
```

### Platform App Authentication Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                   Platform API Request                           │
│                   (api_access_token header)                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              PlatformAppAuthentication Middleware                │
│  - Looks up AccessToken by token value                          │
│  - Validates owner is PlatformApp                               │
│  - Sets platform_app in request                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│              ValidatePlatformPermissible Middleware              │
│  - Checks resource is in platform_app_permissibles              │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. AccessToken Model (EXISTS - Minor Update)

**Location:** `app/Models/AccessToken.php`

**Current Status:** ✅ Exists with correct implementation

**Changes Needed:**
- Rename `regenerate()` to `regenerateToken()` for Rails parity naming

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AccessToken extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'token',
    ];

    protected $hidden = [
        'token', // Hide token in JSON serialization by default
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(64);
            }
        });
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    // Renamed from regenerate() for Rails parity
    public function regenerateToken(): string
    {
        $this->token = Str::random(64);
        $this->save();
        
        return $this->token;
    }
}
```

### 2. AccessTokenable Trait (EXISTS - Minor Update)

**Location:** `app/Models/Concerns/AccessTokenable.php`

**Current Status:** ✅ Exists with correct implementation

**Changes Needed:**
- Add `deleting` event for cascade delete (Rails `dependent: :destroy_async`)
- Update `resetAccessToken()` to call `regenerateToken()` for consistency

```php
<?php

namespace App\Models\Concerns;

use App\Models\AccessToken;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait AccessTokenable
{
    protected static function bootAccessTokenable(): void
    {
        static::created(function ($model) {
            $model->createAccessToken();
        });
        
        // Add cascade delete for Rails parity
        static::deleting(function ($model) {
            $model->accessTokenModel()->delete();
        });
    }

    public function accessTokenModel(): MorphOne
    {
        return $this->morphOne(AccessToken::class, 'owner');
    }

    public function createAccessToken(): AccessToken
    {
        return $this->accessTokenModel()->create();
    }

    public function resetAccessToken(): string
    {
        $accessToken = $this->accessTokenModel;
        if ($accessToken) {
            return $accessToken->regenerateToken();
        }
        
        return $this->createAccessToken()->token;
    }

    public function getAccessTokenAttribute(): ?string
    {
        return $this->accessTokenModel?->token;
    }
}
```

### 3. User Model (EXISTS - Add Trait)

**Location:** `app/Models/User.php`

**Current Status:** ⚠️ Does not use AccessTokenable trait

**Changes Needed:**
- Add `use AccessTokenable` trait
- Remove the custom `getAccessTokenAttribute()` that uses Sanctum tokens

```php
// Add to use statements
use App\Models\Concerns\AccessTokenable;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes, HasAccountRoles, HasAvatar;
    use AccessTokenable; // ADD THIS
    
    // ... existing code ...
    
    // REMOVE this method (AccessTokenable provides it):
    // public function getAccessTokenAttribute(): ?string
    // {
    //     $token = $this->tokens()->latest()->first();
    //     return $token ? $token->token : null;
    // }
}
```

### 4. PlatformApp Model (EXISTS - Add Trait)

**Location:** `app/Models/PlatformApp.php`

**Current Status:** ⚠️ Has inline token generation, not using AccessTokenable

**Changes Needed:**
- Add `use AccessTokenable` trait
- Remove inline `access_token` column usage (use polymorphic relationship instead)
- Remove `booted()` method that generates inline token
- Remove `regenerateAccessToken()` method (use trait's `resetAccessToken()`)

```php
<?php

namespace App\Models;

use App\Models\Concerns\AccessTokenable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformApp extends Model
{
    use HasFactory, AccessTokenable;

    protected $fillable = [
        'name',
    ];

    // Remove $hidden for access_token (now in AccessToken model)

    // Remove booted() method - AccessTokenable handles token creation

    public function permissibles(): HasMany
    {
        return $this->hasMany(PlatformAppPermissible::class);
    }

    // Remove regenerateAccessToken() - use resetAccessToken() from trait
}
```

### 5. AccessTokenAuthentication Middleware (NEW)

**Location:** `app/Http/Middleware/AccessTokenAuthentication.php`

**Status:** ❌ Does not exist - needs to be created

```php
<?php

namespace App\Http\Middleware;

use App\Models\AccessToken;
use App\Models\AgentBot;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('api_access_token') 
            ?? $request->header('HTTP_API_ACCESS_TOKEN');
        
        // If no access token header, let other auth middleware handle it
        if (empty($token)) {
            return $next($request);
        }
        
        $accessToken = AccessToken::where('token', $token)->first();
        
        if (!$accessToken) {
            return response()->json([
                'error' => 'Invalid Access Token'
            ], 401);
        }
        
        $owner = $accessToken->owner;
        $request->attributes->set('access_token', $accessToken);
        $request->attributes->set('access_token_resource', $owner);
        
        // Set authenticated user for User or AgentBot owners
        // This matches Rails: Current.user = @resource if allowed_current_user_type?(@resource)
        if ($owner instanceof User || $owner instanceof AgentBot) {
            Auth::setUser($owner);
        }
        
        return $next($request);
    }
}
```

### 6. ValidateBotAccess Middleware (NEW)

**Location:** `app/Http/Middleware/ValidateBotAccess.php`

**Status:** ❌ Does not exist - needs to be created

```php
<?php

namespace App\Http\Middleware;

use App\Models\AgentBot;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateBotAccess
{
    /**
     * Bot accessible endpoints mapping (matches Rails BOT_ACCESSIBLE_ENDPOINTS)
     * Format: 'controller_path' => ['allowed_actions']
     */
    public const BOT_ACCESSIBLE_ENDPOINTS = [
        'api/v1/accounts/conversations' => [
            'toggleStatus', 'togglePriority', 'store', 'update', 'customAttributes'
        ],
        'api/v1/accounts/conversations/messages' => ['store'],
        'api/v1/accounts/conversations/assignments' => ['store'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Skip validation for non-bot users (matches Rails: return if Current.user.is_a?(User))
        if (!$user instanceof AgentBot) {
            return $next($request);
        }
        
        // Check if endpoint is accessible to bots
        if (!$this->isBotAccessible($request)) {
            return response()->json([
                'error' => 'Access to this endpoint is not authorized for bots'
            ], 401);
        }
        
        return $next($request);
    }

    protected function isBotAccessible(Request $request): bool
    {
        $route = $request->route();
        if (!$route) {
            return false;
        }
        
        $action = $route->getActionMethod();
        $controllerPath = $this->getControllerPath($route);
        
        $allowedActions = self::BOT_ACCESSIBLE_ENDPOINTS[$controllerPath] ?? [];
        
        return in_array($action, $allowedActions);
    }

    protected function getControllerPath($route): string
    {
        // Map Laravel route to Rails-style controller path
        $controller = $route->getControllerClass();
        
        $mapping = [
            'App\Http\Controllers\Api\V1\ConversationsController' => 'api/v1/accounts/conversations',
            'App\Http\Controllers\Api\V1\MessagesController' => 'api/v1/accounts/conversations/messages',
            // Assignments would be handled by ConversationsController in Laravel
        ];
        
        return $mapping[$controller] ?? '';
    }
}
```

### 7. PlatformAppAuthentication Middleware (NEW)

**Location:** `app/Http/Middleware/PlatformAppAuthentication.php`

**Status:** ❌ Does not exist - needs to be created

```php
<?php

namespace App\Http\Middleware;

use App\Models\AccessToken;
use App\Models\PlatformApp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlatformAppAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('api_access_token') 
            ?? $request->header('HTTP_API_ACCESS_TOKEN');
        
        if (empty($token)) {
            return response()->json([
                'error' => 'Invalid access_token'
            ], 401);
        }
        
        $accessToken = AccessToken::where('token', $token)->first();
        
        if (!$accessToken || !$accessToken->owner instanceof PlatformApp) {
            return response()->json([
                'error' => 'Invalid access_token'
            ], 401);
        }
        
        $request->attributes->set('platform_app', $accessToken->owner);
        
        return $next($request);
    }
}
```

### 8. ValidatePlatformPermissible Middleware (NEW)

**Location:** `app/Http/Middleware/ValidatePlatformPermissible.php`

**Status:** ❌ Does not exist - needs to be created

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePlatformPermissible
{
    public function handle(Request $request, Closure $next): Response
    {
        $platformApp = $request->attributes->get('platform_app');
        $resource = $request->attributes->get('resource');
        
        // Skip if no resource to validate
        if (!$resource) {
            return $next($request);
        }
        
        $hasPermission = $platformApp->permissibles()
            ->where('permissible_type', get_class($resource))
            ->where('permissible_id', $resource->id)
            ->exists();
        
        if (!$hasPermission) {
            return response()->json([
                'error' => 'Non permissible resource'
            ], 401);
        }
        
        return $next($request);
    }
}
```

### 9. ProfileController Update (EXISTS - Update Method)

**Location:** `app/Http/Controllers/Api/V1/ProfileController.php`

**Current Status:** ⚠️ Uses Sanctum tokens instead of polymorphic AccessToken

**Changes Needed:**
- Update `resetAccessToken()` to use polymorphic AccessToken

```php
/**
 * Reset access token.
 */
public function resetAccessToken(): JsonResponse
{
    $user = auth()->user();
    
    // Use polymorphic AccessToken instead of Sanctum
    $newToken = $user->resetAccessToken();
    
    // Return user data with new token (matches Rails response)
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'access_token' => $newToken,
    ]);
}
```

### 10. SuperAdmin AccessTokensController Update (EXISTS - Update)

**Location:** `app/Http/Controllers/Api/V1/SuperAdmin/AccessTokensController.php`

**Current Status:** ⚠️ Manages Sanctum PersonalAccessToken instead of polymorphic AccessToken

**Changes Needed:**
- Update to manage polymorphic AccessToken model
- Add owner_type filtering

```php
<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessTokensController extends Controller
{
    /**
     * List all access tokens.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AccessToken::query();

        // Filter by owner_type (matches Rails COLLECTION_FILTERS)
        if ($request->has('owner_type')) {
            $ownerType = $request->input('owner_type');
            // Map simple names to full class names
            $typeMapping = [
                'User' => 'App\Models\User',
                'AgentBot' => 'App\Models\AgentBot',
                'PlatformApp' => 'App\Models\PlatformApp',
            ];
            $query->where('owner_type', $typeMapping[$ownerType] ?? $ownerType);
        }

        // Filter by owner_id
        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->input('owner_id'));
        }

        $tokens = $query->with('owner')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        return response()->json($tokens);
    }

    /**
     * Show token details.
     */
    public function show(AccessToken $accessToken): JsonResponse
    {
        $accessToken->load('owner');

        return response()->json(['data' => $accessToken]);
    }

    /**
     * Revoke (delete) a token.
     */
    public function destroy(AccessToken $accessToken): JsonResponse
    {
        $accessToken->delete();

        return response()->json(['message' => 'Token revoked.']);
    }
}
```

## Data Models

### AccessToken Table Schema (EXISTS)

The table already exists with correct schema:

```sql
CREATE TABLE access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_type VARCHAR(255) NOT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE INDEX access_tokens_token_unique (token),
    INDEX access_tokens_owner_type_owner_id_index (owner_type, owner_id)
);
```

### PlatformApp Table Migration (NEEDED)

If PlatformApp currently has an `access_token` column, we need a migration to remove it:

```php
// Migration: remove_access_token_from_platform_apps
public function up()
{
    Schema::table('platform_apps', function (Blueprint $table) {
        $table->dropColumn('access_token');
    });
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Polymorphic Owner Relationship

*For any* AccessToken with an owner, the owner relationship SHALL correctly resolve to the associated User, AgentBot, or PlatformApp model.

**Validates: Requirements 1.1**

### Property 2: Automatic Token Generation

*For any* AccessToken created without an explicit token value, the system SHALL generate a unique, non-empty token string of 64 characters.

**Validates: Requirements 1.2**

### Property 3: Token Regeneration Produces New Token

*For any* AccessToken, calling `regenerateToken()` SHALL produce a different token than the previous value and persist it to the database.

**Validates: Requirements 1.3**

### Property 4: AccessTokenable Auto-Creates Token

*For any* model using the AccessTokenable trait (User, AgentBot, PlatformApp), creating a new instance SHALL automatically create an associated AccessToken.

**Validates: Requirements 2.1, 2.4, 2.5, 2.6**

### Property 5: Dependent Destroy Cascades

*For any* model using the AccessTokenable trait, deleting the model SHALL also delete its associated AccessToken.

**Validates: Requirements 2.2**

### Property 6: Access Token Header Lookup

*For any* HTTP request with either `api_access_token` or `HTTP_API_ACCESS_TOKEN` header containing a valid token, the middleware SHALL find and load the corresponding AccessToken.

**Validates: Requirements 3.1, 3.2**

### Property 7: Valid Token Sets Resource

*For any* valid access token, the middleware SHALL set the token's owner as the authenticated resource in the request attributes.

**Validates: Requirements 3.3**

### Property 8: User/AgentBot Token Sets Auth User

*For any* valid access token belonging to a User or AgentBot, the middleware SHALL set `Auth::user()` to the token's owner.

**Validates: Requirements 3.4**

### Property 9: Invalid Token Returns 401

*For any* request with an invalid or non-existent access token to a protected route, the system SHALL return a 401 response with message "Invalid Access Token".

**Validates: Requirements 3.5**

### Property 10: Bot Access Restriction

*For any* AgentBot-authenticated request to an endpoint NOT in BOT_ACCESSIBLE_ENDPOINTS, the system SHALL return a 401 response with message "Access to this endpoint is not authorized for bots".

**Validates: Requirements 4.5**

### Property 11: User Bypasses Bot Restrictions

*For any* User-authenticated request (via access token), the system SHALL NOT apply bot endpoint restrictions.

**Validates: Requirements 4.6**

### Property 12: Platform App Authentication

*For any* request to platform routes with a valid PlatformApp access token, the system SHALL authenticate the request and set the platform_app in request attributes.

**Validates: Requirements 5.1**

### Property 13: Non-Platform Token Rejected

*For any* request to platform routes with an access token NOT belonging to a PlatformApp, the system SHALL return a 401 response with message "Invalid access_token".

**Validates: Requirements 5.2**

### Property 14: Permissible Validation

*For any* platform app request accessing a resource, if the resource is NOT in the platform app's permissibles, the system SHALL return a 401 response with message "Non permissible resource".

**Validates: Requirements 5.3, 5.4**

### Property 15: User Token Reset

*For any* authenticated user calling the reset_access_token endpoint, the system SHALL generate a new token different from the previous one and return the updated user with the new token.

**Validates: Requirements 6.2, 6.3**

### Property 16: Bot Token Reset

*For any* administrator calling the bot reset_access_token endpoint, the system SHALL generate a new token different from the previous one and return the updated bot with the new token.

**Validates: Requirements 7.2, 7.3**

### Property 17: Bot Reset Requires Admin

*For any* non-administrator user attempting to call the bot reset_access_token endpoint, the system SHALL return a 403 Forbidden response.

**Validates: Requirements 7.4**

### Property 18: SuperAdmin Filter by Owner Type

*For any* SuperAdmin request to list access tokens with an owner_type filter, the returned tokens SHALL all have the specified owner_type.

**Validates: Requirements 8.2**

## Error Handling

### Authentication Errors

| Error Condition | HTTP Status | Response Message |
|----------------|-------------|------------------|
| Missing/invalid access token | 401 | "Invalid Access Token" |
| Bot accessing restricted endpoint | 401 | "Access to this endpoint is not authorized for bots" |
| Non-PlatformApp token on platform routes | 401 | "Invalid access_token" |
| Platform app accessing non-permissible resource | 401 | "Non permissible resource" |
| Non-admin resetting bot token | 403 | "Unauthorized" |

## Testing Strategy

### Unit Tests

Unit tests will verify specific examples and edge cases:

1. **AccessToken Model Tests**
   - Token auto-generation on create
   - Polymorphic relationship resolution for each owner type
   - Token regeneration method

2. **AccessTokenable Trait Tests**
   - Auto-creation of token on model creation
   - Cascade delete of token on model deletion
   - Manual token creation method

3. **Middleware Tests**
   - Header parsing (both header formats)
   - Token lookup success/failure
   - Auth::user() setting for different owner types

### Property-Based Tests

Property-based tests will use Pest with faker for generating test data. Each property test will run a minimum of 100 iterations.

**Testing Framework:** Pest PHP with Laravel plugin

### Integration Tests

Integration tests will verify end-to-end flows:

1. **API Authentication Flow**
   - Request with valid User token → authenticated as user
   - Request with valid AgentBot token → authenticated as bot
   - Request with valid PlatformApp token → authenticated as platform app
   - Request with invalid token → 401 error

2. **Bot Restriction Flow**
   - Bot accessing permitted endpoint → success
   - Bot accessing restricted endpoint → 401 error
   - User accessing any endpoint → success

3. **Token Reset Flow**
   - User resetting own token → new token returned
   - Admin resetting bot token → new token returned
   - Non-admin resetting bot token → 403 error

4. **Platform App Flow**
   - Platform app accessing permissible resource → success
   - Platform app accessing non-permissible resource → 401 error
