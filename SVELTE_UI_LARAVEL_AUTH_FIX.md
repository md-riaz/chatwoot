# SvelteKit + Laravel Authentication Fix

## Overview

This PR fixes the SvelteKit UI authentication to work with the Laravel backend. The project is migrating from Vue frontend + Rails backend to SvelteKit frontend + Laravel backend.

**IMPORTANT:** The correct SvelteKit directory is `custom/ui/svelte-ui`, not `custom/ui/sveltekit-ui` (which has been deleted).

## Changes Made

### 1. Laravel Backend - UserResource Enhancement

**File**: `custom/laravel/app/Http/Resources/User/UserResource.php`

Added critical fields to the user API response:
- `roles`: Array of role names from Spatie Permission package
- `accounts`: Array of account memberships with role, availability, and activity data

```php
public function toArray(Request $request): array
{
    return [
        // ... existing fields ...
        'roles' => $this->roles->pluck('name'),
        'accounts' => $this->accountUsers->map(fn($accountUser) => [
            'id' => $accountUser->account_id,
            'name' => $accountUser->account->name ?? null,
            'role' => $accountUser->role,
            'availability' => $accountUser->availability,
            'active_at' => $accountUser->active_at?->toISOString(),
        ]),
    ];
}
```

### 2. Frontend Type Definition

**File**: `custom/ui/svelte-ui/src/lib/api/auth.ts`

Updated CurrentUser interface to match Laravel's UserResource:
```typescript
export interface CurrentUser {
    id: number;
    accountId: number;
    accounts: UserAccount[];
    email: string;
    name: string;
    // ... other existing fields ...
    roles?: string[];  // Added for super admin support
}
```

### 3. API Client Configuration

**File**: `custom/ui/svelte-ui/src/lib/api/client.ts`

Configured for Laravel backend:
- **Base URL**: `http://localhost:8000` (Laravel default)
- **Auth Method**: ****** (Laravel Sanctum)
- **Endpoints**:
  - Login: `POST /api/v1/auth/login`
  - Logout: `POST /api/v1/auth/logout`
  - Get User: `GET /api/v1/auth/me`

```typescript
// API client uses ******
const createApiClient = (): KyInstance => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
  
  return ky.create({
    // ...
    hooks: {
      beforeRequest: [
        (request) => {
          const token = getAuthToken();
          if (token) {
            request.headers.set('Authorization', `******;
          }
        }
      ]
    }
  });
};
```

### 4. Super Admin Authorization

**File**: `custom/ui/svelte-ui/src/routes/app/super_admin/+layout.ts`

Uses Spatie Permission roles to check for super admin access:
```typescript
// Check if user has super_admin role
if (!user.roles?.includes('super_admin')) {
    throw redirect(307, '/login?error=not_authorized');
}
```

### 5. Login Flow

**File**: `custom/ui/svelte-ui/src/routes/auth/login/+page.svelte`

Redirects based on user roles:
```typescript
if (response.user.roles?.includes('super_admin')) {
    await goto('/app/super_admin/dashboard');
} else {
    await goto('/app');
}
```

## Authentication Flow

### Login Process

1. User submits email and password
2. Frontend sends `POST /api/v1/auth/login` with credentials
3. Laravel validates and returns:
```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "roles": ["super_admin"],
        "accounts": [
            {
                "id": 1,
                "name": "My Account",
                "role": "administrator",
                "availability": 1
            }
        ]
    }
}
```
4. Frontend stores token in localStorage
5. Checks if user has `super_admin` role
6. Redirects to appropriate dashboard

### API Requests

All subsequent API requests include the ******
```
Authorization: ******
```

### Logout Process

1. User clicks logout
2. Frontend sends `POST /api/v1/auth/logout`
3. Laravel revokes the token
4. Frontend clears localStorage and redirects to login

## Super Admin Access Control

Super admins are identified by having the `super_admin` role (via Spatie Permission). The layout guard checks this before allowing access to super admin routes.

### Role Check
```typescript
user.roles?.includes('super_admin')
```

### Middleware (Laravel side)
The Laravel backend has `EnsureSuperAdmin` middleware that also checks:
```php
$request->user()->hasRole('super_admin')
```

## Directory Structure

```
custom/ui/
├── storybooks/     # Component storybooks
└── svelte-ui/      # ✅ CORRECT: SvelteKit SPA project
```

**Note:** The `custom/ui/sveltekit-ui` directory was created by mistake and has been completely deleted.

## Testing

### Prerequisites

1. **Laravel Backend Running** on port 8000:
```bash
cd custom/laravel
php artisan serve
```

2. **Database with Super Admin User**:
```bash
php artisan db:seed  # Seeds a super admin user
# Or create manually with 'super_admin' role
```

3. **SvelteKit Dev Server**:
```bash
cd custom/ui/svelte-ui
pnpm install
pnpm dev
```

### Test Cases

#### 1. Super Admin Login
- Navigate to `/auth/login`
- Enter super admin credentials
- Should redirect to `/app/super_admin/dashboard`
- User info should display in sidebar
- Logout button should be visible

#### 2. Super Admin Authorization
- After login, manually navigate to `/app/super_admin/accounts`
- Should load successfully (user has super_admin role)
- Try accessing as regular user - should redirect to login

#### 3. Regular User Login
- Login with a user without super_admin role
- Should redirect to `/app` (regular app interface)
- Should NOT be able to access super admin routes

#### 4. API Token Validation
- After login, check localStorage for `auth_token`
- All API requests should include `Authorization: ****** header
- Token should be validated by Laravel on each request

#### 5. Logout
- Click logout button
- Should redirect to `/auth/login`
- Token should be removed from localStorage
- Should not be able to access protected routes

## Files Changed

1. `custom/laravel/app/Http/Resources/User/UserResource.php` - Added roles and accounts
2. `custom/ui/svelte-ui/src/lib/api/auth.ts` - Added roles to CurrentUser
3. `custom/ui/svelte-ui/src/lib/api/client.ts` - Laravel base URL (8000)
4. `custom/ui/svelte-ui/src/routes/app/super_admin/+layout.ts` - Role-based auth
5. `custom/ui/svelte-ui/src/routes/auth/login/+page.svelte` - Role-based redirect

## Configuration

### Environment Variables

**SvelteKit** (`.env` in `custom/ui/svelte-ui`):
```
VITE_API_BASE_URL=http://localhost:8000
```

**Laravel** (`.env` in `custom/laravel`):
```
APP_URL=http://localhost:8000
```

## Security

- ✅ Uses Laravel Sanctum for token-based authentication
- ✅ Tokens are properly validated on each request
- ✅ Super admin access is protected both frontend and backend
- ✅ CodeQL security scan: 0 vulnerabilities
- ✅ Tokens are revoked on logout

## Known Limitations

1. **Regular Account Routes**: Regular account routes already exist in svelte-ui
2. **URL Structure**: Current URLs don't include account ID in some places. Will be addressed in future PR.

## Migration Notes

This codebase was originally using Rails backend. The authentication has been updated to work with Laravel:

| Aspect | Rails (Old) | Laravel (New) |
|--------|-------------|---------------|
| Port | 3000 | 8000 |
| Auth Library | devise_token_auth | Laravel Sanctum |
| Token Format | Headers: access-token, client, uid | ****** |
| User Type Check | `user.type === 'SuperAdmin'` | `user.roles?.includes('super_admin')` |
| Roles System | Built-in type field | Spatie Permission package |
| Response Format | `{ data: { ...user } }` | `{ token, user }` |
| Frontend Dir | N/A | `custom/ui/svelte-ui` |

## Summary

- ✅ All changes applied to correct directory: `custom/ui/svelte-ui`
- ✅ Laravel backend returns roles and accounts data
- ✅ Frontend checks super_admin role for authorization
- ✅ API configured for Laravel (port 8000, ****** auth)
- ✅ Wrong directory (`custom/ui/sveltekit-ui`) completely deleted
- ✅ Build succeeds
- ✅ No security vulnerabilities
