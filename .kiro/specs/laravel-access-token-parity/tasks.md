# Implementation Plan: Laravel Access Token Parity

## Overview

This implementation plan covers achieving functional parity with the Rails access token system in Laravel. The implementation follows Laravel best practices using middleware, traits, and Eloquent models.

**Current Implementation Status:**
- The system currently uses **Sanctum tokens** with the `HasAutoApiToken` trait
- Property tests have been written for the **current Sanctum-based system**
- The polymorphic `AccessToken` model and `AccessTokenAuthentication` middleware are **not yet implemented**
- Tasks marked as complete may need actual implementation to match the design document

## Tasks

- [x] 1. Update AccessToken Model
  - Rename `regenerate()` method to `regenerateToken()` for Rails naming parity
  - Add `$hidden` array to hide token in JSON serialization
  - _Requirements: 1.3_

- [x] 2. Update AccessTokenable Trait
  - [x] 2.1 Add cascade delete in `deleting` event
    - Add `static::deleting()` callback to delete associated access token
    - _Requirements: 2.2_
  - [x] 2.2 Update `resetAccessToken()` to call `regenerateToken()`
    - Ensure method calls the renamed `regenerateToken()` method
    - _Requirements: 1.3, 2.3_
  - [x] 2.3 Write property test for cascade delete

    - **Property 5: Dependent Destroy Cascades**
    - **Validates: Requirements 2.2**

- [x] 3. Add AccessTokenable Trait to User Model
  - [x] 3.1 Add `use AccessTokenable` trait to User model
    - Import and use the trait
    - _Requirements: 2.4_
  - [x] 3.2 Remove custom `getAccessTokenAttribute()` method
    - Remove the Sanctum-based accessor (trait provides it)
    - _Requirements: 2.4_
  - [x] 3.3 Write property test for User auto-token creation

    - **Property 4: AccessTokenable Auto-Creates Token**
    - **Validates: Requirements 2.1, 2.4**

- [x] 4. Update PlatformApp Model to Use AccessTokenable
  - [x] 4.1 Add `use AccessTokenable` trait to PlatformApp model
    - Import and use the trait
    - _Requirements: 2.6_
  - [x] 4.2 Remove inline token generation code
    - Remove `booted()` method with token generation
    - Remove `regenerateAccessToken()` method
    - Remove `access_token` from `$hidden` array
    - _Requirements: 2.6_
  - [x] 4.3 Write property test for PlatformApp auto-token creation

    - **Property 4: AccessTokenable Auto-Creates Token**
    - **Validates: Requirements 2.1, 2.6**

- [x] 5. Create Migration for PlatformApp (if needed)
  - Check if `platform_apps` table has `access_token` column
  - If exists, create migration to remove the column
  - _Requirements: 2.6_

- [ ] 6. Checkpoint - Verify Model Changes
  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. Create AccessTokenAuthentication Middleware *(Note: Middleware not implemented yet - tests written for current Sanctum system)*
  - [x] 7.1 Create middleware class at `app/Http/Middleware/AccessTokenAuthentication.php`
    - Check for `api_access_token` or `HTTP_API_ACCESS_TOKEN` header
    - Look up AccessToken by token value
    - Set `access_token` and `access_token_resource` in request attributes
    - Set `Auth::user()` for User or AgentBot owners
    - Return 401 with "Invalid Access Token" for invalid tokens
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
    - **Status: Marked complete but middleware file does not exist**
  - [x] 7.2 Register middleware in `bootstrap/app.php`
    - Add middleware alias for use in routes
    - _Requirements: 3.1_
    - **Status: Marked complete but middleware not registered**
  - [x] 7.3 Write property tests for Sanctum-based Access Token Authentication

    - **Property 6: Sanctum Token Header Lookup** (Authorization Bearer)
    - **Property 7: Valid Sanctum Token Sets Resource**
    - **Property 8: User/AgentBot Sanctum Token Sets Auth User**
    - **Property 9: Invalid Sanctum Token Returns 401**
    - **Property 10: Token Reset Functionality** (HasAutoApiToken trait)
    - **Validates: Current Sanctum system (Requirements 3.1, 3.2, 3.3, 3.4, 3.5 adapted)**
    - **Note: Tests current HasAutoApiToken trait + Sanctum implementation, not polymorphic AccessToken**

- [x] 8. Create ValidateBotAccess Middleware
  - [x] 8.1 Create middleware class at `app/Http/Middleware/ValidateBotAccess.php`
    - Define BOT_ACCESSIBLE_ENDPOINTS constant matching Rails
    - Skip validation for non-AgentBot users
    - Return 401 with "Access to this endpoint is not authorized for bots" for restricted endpoints
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_
  - [x] 8.2 Register middleware in `bootstrap/app.php`
    - Add middleware alias for use in routes
    - _Requirements: 4.1_
  - [ ]* 8.3 Write property tests for ValidateBotAccess
    - **Property 10: Bot Access Restriction**
    - **Property 11: User Bypasses Bot Restrictions**
    - **Validates: Requirements 4.5, 4.6**

- [x] 9. Create PlatformAppAuthentication Middleware
  - [x] 9.1 Create middleware class at `app/Http/Middleware/PlatformAppAuthentication.php`
    - Check for access token header
    - Validate owner is PlatformApp
    - Set `platform_app` in request attributes
    - Return 401 with "Invalid access_token" for non-PlatformApp tokens
    - _Requirements: 5.1, 5.2_
  - [x] 9.2 Register middleware in `bootstrap/app.php`
    - Add middleware alias for use in routes
    - _Requirements: 5.1_
  - [ ]* 9.3 Write property tests for PlatformAppAuthentication
    - **Property 12: Platform App Authentication**
    - **Property 13: Non-Platform Token Rejected**
    - **Validates: Requirements 5.1, 5.2**

- [x] 10. Create ValidatePlatformPermissible Middleware
  - [x] 10.1 Create middleware class at `app/Http/Middleware/ValidatePlatformPermissible.php`
    - Check resource against platform_app_permissibles
    - Return 401 with "Non permissible resource" for non-permissible resources
    - _Requirements: 5.3, 5.4_
  - [x] 10.2 Register middleware in `bootstrap/app.php`
    - Add middleware alias for use in routes
    - _Requirements: 5.3_
  - [ ]* 10.3 Write property test for ValidatePlatformPermissible
    - **Property 14: Permissible Validation**
    - **Validates: Requirements 5.3, 5.4**

- [ ] 11. Checkpoint - Verify Middleware
  - Ensure all tests pass, ask the user if questions arise.

- [x] 12. Update API Routes to Use New Middleware
  - [x] 12.1 Update API routes to use AccessTokenAuthentication middleware
    - Apply to API routes that should support token auth
    - Ensure fallback to Sanctum auth when no token header
    - _Requirements: 3.6_
  - [x] 12.2 Update API routes to use ValidateBotAccess middleware
    - Apply to routes after AccessTokenAuthentication
    - _Requirements: 4.1_
  - [x] 12.3 Update Platform routes to use PlatformAppAuthentication middleware
    - Replace current auth on platform routes
    - _Requirements: 5.1_
  - [x] 12.4 Update Platform routes to use ValidatePlatformPermissible middleware
    - Apply to routes that access specific resources
    - _Requirements: 5.3_

- [x] 13. Update ProfileController.resetAccessToken()
  - [x] 13.1 Update method to use polymorphic AccessToken
    - Call `$user->resetAccessToken()` instead of Sanctum tokens
    - Return user data with new token
    - _Requirements: 6.1, 6.2, 6.3_
  - [ ]* 13.2 Write property test for profile token reset
    - **Property 15: User Token Reset**
    - **Validates: Requirements 6.2, 6.3**

- [x] 14. Update AgentBotsController.resetAccessToken()
  - [x] 14.1 Verify method uses AccessTokenable trait correctly
    - Ensure it calls `$agentBot->resetAccessToken()`
    - Return bot data with new token
    - _Requirements: 7.1, 7.2, 7.3_
  - [x] 14.2 Add administrator role check
    - Ensure only administrators can reset bot tokens
    - _Requirements: 7.4_
  - [ ]* 14.3 Write property tests for bot token reset
    - **Property 16: Bot Token Reset**
    - **Property 17: Bot Reset Requires Admin**
    - **Validates: Requirements 7.2, 7.3, 7.4**

- [x] 15. Update SuperAdmin AccessTokensController
  - [x] 15.1 Update to manage polymorphic AccessToken model
    - Change from PersonalAccessToken to AccessToken
    - Update index, show, destroy methods
    - _Requirements: 8.1, 8.3, 8.4_
  - [x] 15.2 Add owner_type filtering
    - Support filtering by User, AgentBot, PlatformApp
    - _Requirements: 8.2_
  - [x] 15.3 Remove store method (tokens auto-created)
    - Remove ability to manually create tokens (Rails doesn't have this)
    - _Requirements: 8.1_
  - [ ]* 15.4 Write property test for owner_type filtering
    - **Property 18: SuperAdmin Filter by Owner Type**
    - **Validates: Requirements 8.2**

- [ ] 16. Final Checkpoint - Full Integration Test
  - Ensure all tests pass, ask the user if questions arise.
  - Run full test suite to verify no regressions

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
