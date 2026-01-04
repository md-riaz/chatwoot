# SvelteKit + Laravel Authentication Fix

## Overview

This PR fixes the SvelteKit UI authentication to work with the Laravel backend (not Rails). The project is migrating from Vue frontend + Rails backend to SvelteKit frontend + Laravel backend.

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

**File**: `custom/ui/sveltekit-ui/src/lib/types/index.ts`

Updated User interface to match Laravel's UserResource:
```typescript
export interface UserAccount {
    id: number;
    name: string | null;
    role: string;
    availability?: number;
    active_at?: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    // ... other fields ...
    roles?: string[];        // From Spatie Permission
    accounts?: UserAccount[]; // User's account memberships
}
```

### 3. API Client Configuration

**File**: `custom/ui/sveltekit-ui/src/lib/api/client.ts`

Configured for Laravel backend:
- **Base URL**: `http://localhost:8000/api/v1` (Laravel default)
- **Auth Method**: Bearer token (Laravel Sanctum)
- **Endpoints**:
  - Login: `POST /api/v1/auth/login`
  - Logout: `POST /api/v1/auth/logout`
  - Get User: `GET /api/v1/auth/me`

```typescript
// Authentication uses Bearer token
beforeRequest: [
    (request) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            request.headers.set('Authorization', `Bearer ${token}`);
        }
    }
]
```

### 4. Super Admin Authorization

**File**: `custom/ui/sveltekit-ui/src/routes/app/super_admin/+layout.ts`

Uses Spatie Permission roles to check for super admin access:
```typescript
// Check if user has super_admin role
if (!user.roles?.includes('super_admin')) {
    throw redirect(307, '/login?error=not_authorized');
}
```

### 5. Login Flow

**File**: `custom/ui/sveltekit-ui/src/routes/login/+page.svelte`

Redirects based on user roles:
```typescript
if (user.roles?.includes('super_admin')) {
    goto('/app/super_admin/dashboard');
} else {
    // Regular users - account routes don't exist yet
    toast.error('Regular account interface not yet available...');
    authStore.logout();
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

All subsequent API requests include the Bearer token:
```
Authorization: Bearer 1|abc123...
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
cd custom/ui/sveltekit-ui
pnpm install
pnpm dev
```

### Test Cases

#### 1. Super Admin Login
- Navigate to `/login`
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
- Should see error message
- Auth should be cleared (logout)
- Should NOT be able to access super admin routes

#### 4. API Token Validation
- After login, check localStorage for `auth_token`
- All API requests should include `Authorization: Bearer <token>` header
- Token should be validated by Laravel on each request

#### 5. Logout
- Click logout button
- Should redirect to `/login`
- Token should be removed from localStorage
- Should not be able to access protected routes

## Files Changed

1. `custom/laravel/app/Http/Resources/User/UserResource.php` - Added roles and accounts
2. `custom/ui/sveltekit-ui/src/lib/types/index.ts` - Updated User interface
3. `custom/ui/sveltekit-ui/src/lib/api/client.ts` - Laravel endpoints and Bearer token
4. `custom/ui/sveltekit-ui/src/routes/app/super_admin/+layout.ts` - Role-based auth
5. `custom/ui/sveltekit-ui/src/routes/login/+page.svelte` - Role-based redirect

## Configuration

### Environment Variables

**SvelteKit** (`.env`):
```
VITE_API_URL=http://localhost:8000/api/v1
```

**Laravel** (`.env`):
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

1. **Regular Account Routes**: Not yet implemented. Regular users cannot login until account routes are created.
2. **URL Structure**: Current URLs don't include account ID (`/app/settings/inboxes` vs `/app/accounts/:accountId/settings/inboxes`). Will be addressed in separate PR.

## Migration from Rails

This codebase was originally using Rails backend. The authentication has been updated to work with Laravel:

| Aspect | Rails (Old) | Laravel (New) |
|--------|-------------|---------------|
| Port | 3000 | 8000 |
| Auth Library | devise_token_auth | Laravel Sanctum |
| Token Format | Headers: access-token, client, uid | Bearer token |
| User Type Check | `user.type === 'SuperAdmin'` | `user.roles?.includes('super_admin')` |
| Roles System | Built-in type field | Spatie Permission package |
| Login Endpoint | `/auth/sign_in` | `/api/v1/auth/login` |
| Response Format | `{ data: { ...user } }` | `{ token, user }` |

## Next Steps

1. Test the authentication flow with Laravel backend
2. Create regular account routes for non-super-admin users
3. Implement URL structure with account ID
4. Add account switching functionality
