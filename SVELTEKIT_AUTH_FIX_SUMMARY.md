# SvelteKit Authentication & User Type Fixes - Summary

## Changes Made

This PR fixes critical issues with the SvelteKit UI authentication flow and user data handling.

### 1. User Type Definition (✅ COMPLETE)

**File**: `custom/ui/sveltekit-ui/src/lib/types/index.ts`

**Changes**:
- Added `UserAccount` interface for the accounts array in User
- Updated `User` interface to include all fields from Rails backend:
  - `type?: 'User' | 'SuperAdmin'` - Critical for identifying super admin users
  - `access_token?: string`
  - `account_id?: number`
  - `accounts?: UserAccount[]` - Array of accounts user belongs to
  - `inviter_id?: number`
  - `provider?: string`
  - `pubsub_token?: string`
  - `hmac_identifier?: string`
  - `message_signature?: string`
  - `uid?: string`
  - `custom_attributes?: Record<string, unknown>`
  - `ui_settings?: Record<string, unknown>`
  - `available_name?: string`
  - `confirmed?: boolean`

**Impact**: Frontend can now access all user data returned by the backend

### 2. Authentication Flow (✅ COMPLETE)

**Files Modified**:
- `custom/ui/sveltekit-ui/src/lib/api/client.ts`
- `custom/ui/sveltekit-ui/src/lib/stores/auth.ts`
- `custom/ui/sveltekit-ui/src/routes/login/+page.svelte`
- `custom/ui/sveltekit-ui/src/routes/onboarding/+page.svelte`
- `custom/ui/sveltekit-ui/.env.example`

**Changes**:
- **API Base URL**: Changed from `http://localhost:8000/api/v1` to `http://localhost:3000`
- **Auth Headers**: Switched from Bearer token to devise_token_auth format:
  - `access-token`: The authentication token
  - `client`: Client identifier
  - `uid`: User identifier (email)
- **API Endpoints**:
  - Login: `POST /auth/sign_in` (was `/login`)
  - Logout: `DELETE /auth/sign_out` (was `POST /logout`)
  - Get Current User: `GET /auth/validate_token` (was `GET /me`)
- **Response Format**: Updated to handle `{ data: { ...user } }` wrapper
- **Token Storage**: Now stores all three tokens (access-token, client, uid) in localStorage
- **Automatic Token Refresh**: API client automatically updates tokens from response headers

### 3. Super Admin Authorization (✅ COMPLETE)

**File**: `custom/ui/sveltekit-ui/src/routes/app/super_admin/+layout.ts`

**Changes**:
- Added check for `user.type === 'SuperAdmin'`
- Redirects non-super-admin users to login with error
- Properly cleans up all auth tokens on authorization failure

### 4. Logout Button (✅ ALREADY WORKING)

**File**: `custom/ui/sveltekit-ui/src/routes/app/super_admin/+layout.svelte`

**Status**: Already visible and functional in lines 102-110
- Shows user name and email
- Logout button triggers `authStore.logout()` and redirects to `/login`

## Testing Instructions

### Prerequisites

1. **Rails Backend Running**: Ensure Chatwoot Rails server is running on port 3000
   ```bash
   cd /home/runner/work/chatwoot/chatwoot
   bundle install
   rails server -p 3000
   ```

2. **Database Setup**: Ensure database has at least one super admin user
   ```bash
   rails db:migrate
   # Create a super admin user if needed
   rails console
   > User.create!(email: 'admin@example.com', password: 'password123', type: 'SuperAdmin', name: 'Admin User')
   ```

3. **SvelteKit Dev Server**: Run the frontend
   ```bash
   cd custom/ui/sveltekit-ui
   pnpm install
   pnpm dev
   ```

### Test Cases

#### Test 1: Login Flow
1. Navigate to `http://localhost:5173/login`
2. Enter super admin credentials
3. Click "Login"
4. **Expected**: 
   - Redirects to `/app/super_admin/dashboard`
   - User info visible in sidebar
   - All auth headers stored in localStorage

#### Test 2: User Data
1. After successful login, open browser console
2. Run: `JSON.parse(localStorage.getItem('user'))`
3. **Expected**: Should see all user fields including:
   - `type: "SuperAdmin"`
   - `accounts: [...]`
   - `account_id: <number>`
   - Other fields from backend

#### Test 3: Auth Headers
1. After login, check localStorage
2. **Expected**: Should see three items:
   - `auth_token`: access token value
   - `auth_client`: client identifier
   - `auth_uid`: user email

#### Test 4: Super Admin Protection
1. Login with a regular user (not super admin)
2. Try to access `/app/super_admin/dashboard`
3. **Expected**: 
   - Should redirect to `/login?error=not_authorized`
   - Auth tokens cleared from localStorage

#### Test 5: Logout
1. Login as super admin
2. Click logout button in sidebar
3. **Expected**:
   - Redirects to `/login`
   - All auth tokens removed from localStorage
   - Cannot access super admin pages without re-login

#### Test 6: Token Validation
1. Login successfully
2. Wait for token to expire (or manually invalidate in Rails console)
3. Try to navigate or perform an action
4. **Expected**:
   - Automatic redirect to `/login`
   - Auth tokens cleared

## Known Issues (Out of Scope)

### 1. URL Structure Mismatch
**Status**: Explicitly excluded per problem statement
- Current: `/app/settings/inboxes`
- Expected: `/app/accounts/:accountId/settings/inboxes`
- **Action**: Will be addressed in separate PR

### 2. Super Admin Navigation Link
**Status**: Not applicable yet
- No regular account routes exist yet (`/app/accounts/:accountId/*`)
- Super admin link should appear in navigation when user is a super admin
- **Action**: Implement when regular account routes are created

### 3. Pre-existing TypeScript Errors
**Status**: Not fixed in this PR
- Many TypeScript errors exist in other files
- These are unrelated to the authentication fixes
- **Action**: Should be addressed in separate PRs

## Files Changed

1. `custom/ui/sveltekit-ui/src/lib/types/index.ts` - User type definitions
2. `custom/ui/sveltekit-ui/src/lib/api/client.ts` - API client configuration
3. `custom/ui/sveltekit-ui/src/lib/stores/auth.ts` - Auth store
4. `custom/ui/sveltekit-ui/src/routes/login/+page.svelte` - Login page
5. `custom/ui/sveltekit-ui/src/routes/onboarding/+page.svelte` - Onboarding page
6. `custom/ui/sveltekit-ui/src/routes/app/super_admin/+layout.ts` - Super admin guard
7. `custom/ui/sveltekit-ui/.env.example` - Environment variables example

## Verification Checklist

- [x] User interface includes all backend fields
- [x] API client uses correct Rails endpoints
- [x] Auth headers follow devise_token_auth format
- [x] Login stores all required tokens
- [x] Super admin routes check user type
- [x] Logout clears all auth data
- [x] Token validation redirects properly
- [x] Onboarding flow updated
- [ ] Manual testing completed (requires running backend)
- [ ] End-to-end login/logout flow verified

## Migration Notes

### For Developers

If you're working on this codebase, note that:

1. **API Base URL**: Default is now `http://localhost:3000` (Rails), not `8000` (Laravel)
2. **Auth Format**: Uses devise_token_auth, not Bearer tokens
3. **User Type Check**: Always check `user.type === 'SuperAdmin'` for super admin features
4. **Auth Headers**: Three headers required: access-token, client, uid

### For Deployment

1. Set `VITE_API_URL` environment variable to point to Rails API
2. Ensure Rails backend is configured with devise_token_auth
3. Verify CORS settings allow the required auth headers

## Related Documentation

- Rails Backend User Model: `/app/views/api/v1/models/_user.json.jbuilder`
- Rails Auth Controller: `/app/controllers/devise_overrides/sessions_controller.rb`
- devise_token_auth gem: https://github.com/lynndylanhurley/devise_token_auth
