# Requirements Document

## Introduction

This specification defines the requirements for implementing a comprehensive Platform Apps management interface in SvelteKit to replace the basic Rails Administrate interface. The current Rails implementation uses Administrate (a basic admin gem) which provides only standard CRUD operations. The new SvelteKit interface will provide enhanced functionality and better user experience while maintaining API compatibility with the existing Laravel backend.

## Glossary

- **Platform_App**: An external application registered in Chatwoot that can access APIs via access tokens
- **Access_Token**: A secure token that allows Platform Apps to authenticate API requests
- **Permissible**: A user or resource that a Platform App has permission to access
- **Super_Admin**: Administrator with system-wide access to manage Platform Apps
- **Laravel_API**: The new Laravel backend that will replace Rails
- **SvelteKit_Frontend**: The new SvelteKit frontend that will replace Vue
- **Rails_Backend**: The current Ruby on Rails backend being migrated from
- **Vue_Frontend**: The current Vue.js frontend being migrated from

## Requirements

### Requirement 1: Platform App Creation Page (New Feature)

**User Story:** As a Super Admin, I want to create new Platform Apps through a dedicated form, so that I can enable third-party integrations with better UX than the basic Rails admin interface.

#### Acceptance Criteria

1. WHEN a Super Admin clicks "New Platform App", THE System SHALL navigate to /app/super_admin/platform-apps/new
2. WHEN the creation page loads, THE System SHALL display a form with name field and proper validation
3. WHEN a Super Admin submits valid data, THE System SHALL create the Platform App and show success feedback
4. WHEN Platform App creation succeeds, THE System SHALL redirect to the detail page showing the generated token
5. WHEN Platform App creation fails, THE System SHALL display validation errors and maintain form data

### Requirement 2: Enhanced Platform App Detail Page (Improvement over Rails)

**User Story:** As a Super Admin, I want to view and edit Platform App details with better token management than the basic Rails admin, so that I can manage app configurations securely and efficiently.

#### Acceptance Criteria

1. WHEN viewing Platform App details, THE System SHALL display name, creation date, and access token with visibility toggle
2. WHEN a Super Admin clicks the eye icon, THE System SHALL toggle token visibility between masked and full display
3. WHEN the token is visible, THE System SHALL provide a copy-to-clipboard button for easy copying
4. WHEN a Super Admin edits the name field, THE System SHALL validate and save changes with proper feedback
5. THE System SHALL provide better UX than the basic Rails Administrate interface

### Requirement 3: API Client Method Completion (Missing Methods)

**User Story:** As a developer, I want the SvelteKit API client to have all necessary Platform App methods, so that the frontend can interact with the Laravel backend properly.

#### Acceptance Criteria

1. THE API client SHALL include proper error handling for all Platform App operations
2. THE API client SHALL use proper TypeScript interfaces for all Platform App operations
3. THE API client SHALL follow the established patterns used by other SuperAdmin API methods
4. THE API client SHALL handle the Laravel pagination format correctly
5. THE API client SHALL provide consistent response handling across all methods

### Requirement 4: Data Display Corrections (Current Issues)

**User Story:** As a Super Admin, I want the Platform Apps list to display accurate information, so that I can properly manage the applications.

#### Acceptance Criteria

1. THE Platform Apps list SHALL remove the non-existent webhook_url column from display
2. THE Platform Apps list SHALL display access_token in masked format with option to reveal
3. THE Platform Apps list SHALL show proper creation and update timestamps
4. THE Platform Apps list SHALL maintain consistent column formatting with other SuperAdmin pages
5. THE Platform Apps list SHALL handle empty states and loading states properly

### Requirement 5: Navigation and Routing Completion (Missing Routes)

**User Story:** As a Super Admin, I want seamless navigation between Platform App pages, so that I can efficiently manage applications.

#### Acceptance Criteria

1. THE System SHALL create the missing /app/super_admin/platform-apps/new route and page
2. THE System SHALL ensure proper navigation from list to detail to edit pages
3. THE System SHALL handle browser back/forward navigation correctly
4. THE System SHALL maintain consistent breadcrumb navigation patterns
5. THE System SHALL redirect appropriately after create/update/delete operations

### Requirement 6: Laravel API Validation and Error Handling (Enhancement)

**User Story:** As a system integrator, I want the Laravel API to provide robust validation and error handling, so that the frontend can provide good user feedback.

#### Acceptance Criteria

1. THE Laravel API SHALL return consistent response formats across all Platform App endpoints
2. THE Laravel API SHALL handle validation errors with proper HTTP status codes and detailed error messages
3. THE Laravel API SHALL provide proper error responses for non-existent Platform Apps (404 with message)
4. THE Laravel API SHALL validate Platform App name uniqueness and provide clear error messages
5. THE Laravel API SHALL follow Laravel standard pagination format for the index endpoint

### Requirement 7: Security and Token Display (Enhanced UX)

**User Story:** As a security administrator, I want Platform App tokens to be handled securely with good UX, so that sensitive information is protected while remaining accessible.

#### Acceptance Criteria

1. THE System SHALL display access tokens with eye icon toggle for visibility (masked by default)
2. THE System SHALL provide copy-to-clipboard functionality when tokens are visible
3. THE System SHALL show success feedback when tokens are copied successfully
4. THE System SHALL not store or log full tokens in browser history or console
5. THE System SHALL mask tokens in list views but allow revealing individual tokens

### Requirement 8: User Experience Improvements (Better than Rails Admin)

**User Story:** As a Super Admin, I want intuitive and modern user interactions, so that Platform App management is more efficient than the basic Rails admin interface.

#### Acceptance Criteria

1. THE System SHALL provide loading states during all API operations
2. THE System SHALL show success/error toast notifications for all operations
3. THE System SHALL maintain form state during validation errors
4. THE System SHALL provide confirmation dialogs for destructive operations (delete)
5. THE System SHALL follow modern UI patterns with proper spacing, typography, and interactions

### Requirement 9: TypeScript Interface Completion (Type Safety)

**User Story:** As a developer, I want proper TypeScript interfaces for Platform Apps, so that the code is type-safe and maintainable.

#### Acceptance Criteria

1. THE System SHALL define complete PlatformApp interface matching the Laravel model structure
2. THE System SHALL include proper types for API response formats (with Laravel pagination)
3. THE System SHALL ensure API client methods have correct return types
4. THE System SHALL maintain consistency with other SuperAdmin TypeScript interfaces
5. THE System SHALL include proper error handling types for Platform App operations

### Requirement 10: Functional Completeness (Full CRUD Operations)

**User Story:** As a Super Admin, I want complete Platform App management functionality, so that I can perform all necessary operations through the modern interface.

#### Acceptance Criteria

1. THE System SHALL successfully create Platform Apps through the SvelteKit UI
2. THE System SHALL successfully list and search Platform Apps with pagination
3. THE System SHALL successfully view and update Platform App details
4. THE System SHALL successfully delete Platform Apps with proper confirmation
5. THE System SHALL handle all error cases gracefully with appropriate user feedback