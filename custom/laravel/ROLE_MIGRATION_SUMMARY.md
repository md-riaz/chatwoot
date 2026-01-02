# Role Value Migration Summary

## Overview
This document summarizes the changes made to migrate from the old role system to Rails-compatible role values.

## Role Value Changes
- **Old System**: `1 = agent`, `2 = admin`
- **New System**: `0 = agent`, `1 = administrator` (Rails parity)

## Files Updated

### 1. Database Layer
- ✅ `database/migrations/2024_01_01_000039_create_account_users_table.php`
  - Updated default role value from `1` to `0`
  - Updated comments to reflect new mapping

### 2. Models
- ✅ `app/Models/AccountUser.php`
  - Updated `getRoleNameAttribute()` method
  - Updated `scopeRole()` method
  - Updated `hasDefaultPermission()` method
  - Updated `getPermissions()` method

### 3. Controllers
- ✅ `app/Http/Controllers/Api/V1/InstallationOnboardingController.php`
  - Updated role assignment to use `1` for administrator
  - Removed `subscribe_to_updates` parameter
  - Removed ChatwootHub registration functionality

- ✅ `app/Http/Controllers/Api/V1/Platform/AccountUsersController.php`
  - Updated role mapping: `administrator ? 1 : 0`

- ✅ `app/Http/Controllers/Api/V1/AgentsController.php`
  - Updated role mapping in both create and update methods

### 4. Services
- ✅ `app/Services/Reports/V2/Reports/AgentSummaryBuilder.php`
  - Updated to exclude role `1` (administrator) instead of string comparison

- ✅ `app/Services/PermissionFilterService.php`
  - Updated administrator check to use `role === 1`

### 5. Models (Additional)
- ✅ `app/Models/SlaEvent.php`
  - Updated administrator query to use `role = 1`

### 6. Jobs
- ✅ `app/Jobs/SendReauthorizationNotificationJob.php`
  - Updated admin user query to use `role = 1`

### 7. Mail Classes
- ✅ `app/Mail/TeamNotifications/AutomationNotificationMail.php`
- ✅ `app/Mail/PortalInstructionsMail.php`
- ✅ `app/Mail/AdministratorNotifications/AccountNotificationMail.php`
- ✅ `app/Mail/AdministratorNotifications/IntegrationsNotificationMail.php`
- ✅ `app/Mail/AdministratorNotifications/ChannelNotificationMail.php`
  - All updated to use `role = 1` for administrator queries

### 8. Middleware
- ✅ `app/Http/Middleware/EnsureAccountAdmin.php`
  - Updated role check from `< 2` to `< 1`
  - Updated documentation

### 9. Policies
- ✅ `app/Policies/MessagePolicy.php`
- ✅ `app/Policies/InboxPolicy.php`
- ✅ `app/Policies/AssignmentPolicy.php`
- ✅ `app/Policies/AccountPolicy.php`
  - All updated to use `role = 1` for administrator checks

### 10. Tests (49 files updated)
- ✅ All test files in `tests/` directory
  - Updated helper functions in `tests/Pest.php`
  - Updated all hardcoded role values throughout test suite
  - Admin role: `2` → `1`
  - Agent role: `1` → `0`

### 11. Seeders
- ✅ `database/seeders/RolesAndPermissionsSeeder.php`
  - Updated to use `firstOrCreate()` instead of `create()`
  - Simplified permission creation with array loop
  - Maintained Rails parity for role structure

### 12. OpenAPI Documentation
- ✅ `docs/openapi/paths/super_admin.yaml`
- ✅ `docs/openapi/openapi.bundled.yaml`
  - Removed `subscribe_to_updates` parameter from onboarding endpoint

## Breaking Changes

### API Changes
1. **Onboarding Endpoint** (`POST /installation/onboarding`)
   - Removed `subscribe_to_updates` parameter
   - No longer performs ChatwootHub registration

### Database Changes
1. **account_users table**
   - Role values changed: `0=agent, 1=administrator`
   - Default role changed from `1` to `0`

### Behavioral Changes
1. **Role Checks**
   - All role-based authorization now uses integer values instead of strings
   - Administrator checks now use `role === 1` instead of `role === 2`
   - Agent checks now use `role === 0` instead of `role === 1`

## Migration Steps Required

1. **Database Migration**
   ```bash
   php artisan db:wipe
   php artisan migrate
   php artisan db:seed
   ```

2. **Clear Application Cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

3. **Run Tests**
   ```bash
   php artisan test
   ```

## Verification Checklist

- [ ] Onboarding endpoint works correctly
- [ ] Role-based permissions function properly
- [ ] Admin users can access admin-only features
- [ ] Agent users have appropriate limited access
- [ ] All tests pass
- [ ] API documentation is accurate

## Notes

- The migration maintains Rails backend parity for role values
- All string-based role comparisons have been converted to integer comparisons
- The Spatie Permission system continues to work alongside the account-level roles
- Custom roles functionality remains unchanged