# ClearLine Laravel Authorization & Permissions

This document describes the authorization and permission system used in the ClearLine Laravel application.

---

## Overview

ClearLine uses a multi-layered authorization approach:

1. **Authentication** - Sanctum token-based authentication
2. **Middleware** - Route-level access control
3. **Policies** - Model-level authorization
4. **Role-Based Access** - Spatie Permission package

---

## User Roles

### Account-Level Roles

Users have roles within each account they belong to, stored in the `account_users` pivot table.

| Role | Value | Description |
|------|-------|-------------|
| **Agent** | 1 | Standard team member |
| **Administrator** | 2 | Full account access |

### System-Level User Types

System-wide user types stored in the `users.type` field (Rails STI compatibility).

| Type | Description |
|------|-------------|
| **User** | Regular user with account-level roles |
| **SuperAdmin** | Platform administrator with access to all accounts |

---

## Middleware

### EnsureAccountAccess

**Location:** `app/Http/Middleware/EnsureAccountAccess.php`

**Purpose:** Validates that the authenticated user has access to the requested account.

**Usage:**
```php
Route::prefix('accounts/{account}')
    ->middleware(\App\Http\Middleware\EnsureAccountAccess::class)
    ->group(function () {
        // Account-scoped routes
    });
```

**Implementation:**
```php
public function handle(Request $request, Closure $next): Response
{
    $account = $request->route('account');
    $user = $request->user();
    
    // Check if user has access to this account
    $hasAccess = $account->users()->where('user_id', $user->id)->exists();
    
    if (!$hasAccess) {
        return response()->json(['error' => 'Account not found'], 404);
    }
    
    return $next($request);
}
```

### EnsureSuperAdmin

**Location:** `app/Http/Middleware/EnsureSuperAdmin.php`

**Purpose:** Restricts access to super administrators only.

**Usage:**
```php
Route::prefix('super_admin')
    ->middleware([EnsureSuperAdmin::class])
    ->group(function () {
        // Super admin routes
    });
```

**Implementation:**
```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    
    // Check the type field (Rails parity) - super_admin is now a user type, not a role
    if (! $user || $user->type !== 'SuperAdmin') {
        return response()->json([
            'error' => 'Unauthorized. Super admin access required.',
        ], 403);
    }
    
    return $next($request);
}
```

---

## Policies

Laravel policies are used for model-level authorization.

### AccountPolicy

**Location:** `app/Policies/AccountPolicy.php`

| Method | Agent | Admin | Logic |
|--------|-------|-------|-------|
| viewAny | ✅ | ✅ | All authenticated users |
| view | ✅ | ✅ | Must be member of account |
| create | ✅ | ✅ | All authenticated users |
| update | ❌ | ✅ | Must be admin (role = 2) |
| delete | ❌ | ✅ | Must be admin (role = 2) |

**Usage in Controller:**
```php
public function update(Request $request, Account $account)
{
    $this->authorize('update', $account);
    // Update logic
}
```

### ConversationPolicy

**Location:** `app/Policies/ConversationPolicy.php`

| Method | Agent | Admin | Logic |
|--------|-------|-------|-------|
| viewAny | ✅ | ✅ | All authenticated users |
| view | ✅ | ✅ | Must be member of account |
| create | ✅ | ✅ | All authenticated users |
| update | ✅ | ✅ | Must be member of account |
| delete | ❌ | ✅ | Must be admin (role = 2) |
| assign | ✅ | ✅ | Must be member of account |

### InboxPolicy

**Location:** `app/Policies/InboxPolicy.php`

| Method | Agent | Admin | Logic |
|--------|-------|-------|-------|
| viewAny | ✅ | ✅ | All authenticated users |
| view | ✅ | ✅ | Must be member of account |
| create | ✅ | ✅ | All authenticated users |
| update | ❌ | ✅ | Must be admin (role = 2) |
| delete | ❌ | ✅ | Must be admin (role = 2) |

### ContactPolicy

**Location:** `app/Policies/ContactPolicy.php`

| Method | Agent | Admin | Logic |
|--------|-------|-------|-------|
| viewAny | ✅ | ✅ | All authenticated users |
| view | ✅ | ✅ | Must be member of account |
| create | ✅ | ✅ | All authenticated users |
| update | ✅ | ✅ | Must be member of account |
| delete | ✅ | ✅ | Must be member of account |

### MessagePolicy

**Location:** `app/Policies/MessagePolicy.php`

| Method | Agent | Admin | Logic |
|--------|-------|-------|-------|
| viewAny | ✅ | ✅ | All authenticated users |
| view | ✅ | ✅ | Must be member of account |
| create | ✅ | ✅ | All authenticated users |
| update | ✅ | ✅ | Must be member of account |
| delete | ✅ | ✅ | Must be member of account |

---

## Route Protection Pattern

### Standard Account Routes

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('accounts/{account}')
        ->middleware(\App\Http\Middleware\EnsureAccountAccess::class)
        ->group(function () {
            
            // All members can access
            Route::apiResource('conversations', ConversationsController::class);
            Route::apiResource('contacts', ContactsController::class);
            
            // Admin-only (checked in controller via policy)
            Route::apiResource('inboxes', InboxesController::class);
            Route::apiResource('webhooks', WebhooksController::class);
        });
});
```

### Super Admin Routes

```php
Route::middleware(['auth:sanctum', EnsureSuperAdmin::class])
    ->prefix('super_admin')
    ->group(function () {
        Route::apiResource('accounts', SuperAdminAccountsController::class);
        Route::apiResource('users', SuperAdminUsersController::class);
    });
```

### Public Routes (Widget/Platform)

```php
// Widget routes - no auth middleware, uses token in header
Route::prefix('widget')->group(function () {
    Route::post('config', [WidgetConfigsController::class, 'create']);
    Route::get('conversations', [WidgetConversationsController::class, 'index']);
});

// Public inbox routes - no auth required
Route::prefix('public')->group(function () {
    Route::prefix('inboxes/{inbox}')->group(function () {
        Route::post('contacts', [PublicContactsController::class, 'store']);
    });
});
```

---

## Controller Authorization Patterns

### Using Policies

```php
class ConversationsController extends Controller
{
    public function show(Account $account, Conversation $conversation)
    {
        // Automatic policy check
        $this->authorize('view', $conversation);
        
        return new ConversationResource($conversation);
    }
    
    public function destroy(Account $account, Conversation $conversation)
    {
        // Only admins can delete
        $this->authorize('delete', $conversation);
        
        $conversation->delete();
        return response()->json(null, 204);
    }
}
```

### Using abort_unless

```php
public function update(Request $request, Account $account, Conversation $conversation)
{
    // Quick inline check
    abort_unless($conversation->account_id === $account->id, 404);
    
    // Update logic
}
```

### Checking Role Directly

```php
public function store(Request $request, Account $account)
{
    // Check if user is admin of this account
    $isAdmin = $request->user()->accounts()
        ->wherePivot('role', 2)
        ->where('account_id', $account->id)
        ->exists();
    
    if (!$isAdmin) {
        return response()->json(['error' => 'Admin access required'], 403);
    }
    
    // Create logic
}
```

---

## Best Practices

### 1. Always Use Middleware for Account Access

```php
// Good
Route::prefix('accounts/{account}')
    ->middleware(EnsureAccountAccess::class)
    ->group(fn() => ...);

// Bad - manually checking in every controller
public function index(Account $account) {
    if (!auth()->user()->accounts->contains($account)) { ... }
}
```

### 2. Use Policies for Model Authorization

```php
// Good
$this->authorize('delete', $conversation);

// Bad - inline role check
if ($user->pivot->role !== 2) { ... }
```

### 3. Scope Queries to Account

```php
// Good
$conversations = Conversation::where('account_id', $account->id)->get();

// Bad - potential data leak
$conversations = Conversation::all();
```

### 4. Return 404 for Unauthorized Access

```php
// Good - doesn't reveal existence
if (!$hasAccess) {
    return response()->json(['error' => 'Not found'], 404);
}

// Bad - reveals resource exists
if (!$hasAccess) {
    return response()->json(['error' => 'Forbidden'], 403);
}
```

---

## Permission Requirements by Feature

### Core Features

| Feature | Agent | Admin | Super Admin |
|---------|-------|-------|-------------|
| View Conversations | ✅ | ✅ | ✅ |
| Reply to Conversations | ✅ | ✅ | ✅ |
| Assign Conversations | ✅ | ✅ | ✅ |
| Delete Conversations | ❌ | ✅ | ✅ |
| View Contacts | ✅ | ✅ | ✅ |
| Manage Contacts | ✅ | ✅ | ✅ |
| View Reports | ❌ | ✅ | ✅ |

### Account Management

| Feature | Agent | Admin | Super Admin |
|---------|-------|-------|-------------|
| Update Account Settings | ❌ | ✅ | ✅ |
| Create Inboxes | ❌ | ✅ | ✅ |
| Manage Teams | ❌ | ✅ | ✅ |
| Configure Integrations | ❌ | ✅ | ✅ |
| Manage Webhooks | ❌ | ✅ | ✅ |
| View Audit Logs | ❌ | ✅ | ✅ |

### Platform Management

| Feature | Agent | Admin | Super Admin |
|---------|-------|-------|-------------|
| Create Accounts | ❌ | ❌ | ✅ |
| Delete Accounts | ❌ | ❌ | ✅ |
| Manage All Users | ❌ | ❌ | ✅ |
| System Configuration | ❌ | ❌ | ✅ |
| View System Status | ❌ | ❌ | ✅ |

---

## Testing Authorization

### Testing Policy

```php
test('agent cannot delete conversation', function () {
    $account = Account::factory()->create();
    $agent = User::factory()->create();
    $account->users()->attach($agent, ['role' => 1]); // Agent
    
    $conversation = Conversation::factory()->for($account)->create();
    
    $response = $this->actingAs($agent)
        ->deleteJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}");
    
    $response->assertForbidden();
});

test('admin can delete conversation', function () {
    $account = Account::factory()->create();
    $admin = User::factory()->create();
    $account->users()->attach($admin, ['role' => 2]); // Admin
    
    $conversation = Conversation::factory()->for($account)->create();
    
    $response = $this->actingAs($admin)
        ->deleteJson("/api/v1/accounts/{$account->id}/conversations/{$conversation->id}");
    
    $response->assertNoContent();
});
```

### Testing Middleware

```php
test('user cannot access other accounts', function () {
    $account1 = Account::factory()->create();
    $account2 = Account::factory()->create();
    $user = User::factory()->create();
    $account1->users()->attach($user); // Only member of account1
    
    $response = $this->actingAs($user)
        ->getJson("/api/v1/accounts/{$account2->id}/conversations");
    
    $response->assertNotFound();
});
```

---

**Last Updated:** 2025-12-27
**Version:** 7.0.0
