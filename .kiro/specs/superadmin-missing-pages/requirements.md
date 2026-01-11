# Requirements Document

## Introduction

This specification covers implementing the missing SuperAdmin pages in the SvelteKit frontend to achieve functional parity with the Rails + Vue SuperAdmin system. Based on analysis of the Rails system, these pages should be implemented as simple utility pages, not primary navigation items.

## Glossary

- **SuperAdmin**: Administrative user type with system-wide access
- **Administrate**: Rails gem providing basic CRUD interfaces
- **Installation_Config**: System-wide configuration settings
- **Access_Token**: API authentication tokens for users/bots/apps
- **Account_User**: Relationship between users and accounts with roles
- **Utility_Page**: Secondary page accessed via direct URL, not main navigation

## Requirements

### Requirement 1: Installation Configs Management

**User Story:** As a SuperAdmin, I want to manage system-wide configuration settings, so that I can control application behavior and integrations.

#### Acceptance Criteria

1. WHEN accessing `/app/super_admin/installation-configs`, THE System SHALL display a configuration management interface
2. WHEN viewing configurations, THE System SHALL group configs by category (general, facebook, email, etc.)
3. WHEN editing a configuration, THE System SHALL validate the input based on config type (boolean, text, secret, select)
4. WHEN saving configurations, THE System SHALL update only unlocked configs and show validation errors
5. THE System SHALL display config descriptions and help text where available
6. WHEN a config is locked, THE System SHALL prevent editing and show locked status

### Requirement 2: Access Tokens Management

**User Story:** As a SuperAdmin, I want to view and manage API access tokens, so that I can monitor and control API access security.

#### Acceptance Criteria

1. WHEN accessing `/app/super_admin/access-tokens`, THE System SHALL display all access tokens with owner information
2. WHEN viewing tokens, THE System SHALL show token name, owner type (User/AgentBot/PlatformApp), and creation date
3. WHEN filtering tokens, THE System SHALL support filtering by owner type
4. WHEN viewing token details, THE System SHALL show abilities, last used date, and expiration
5. WHEN revoking a token, THE System SHALL delete the token and show confirmation
6. THE System SHALL mask token values for security (show only partial token)

### Requirement 3: Account Users Embedded Management

**User Story:** As a SuperAdmin, I want to manage user-account relationships directly from account and user pages, so that I can efficiently assign roles without navigating to separate pages.

#### Acceptance Criteria

1. WHEN viewing an account details page, THE System SHALL display an embedded form to add users to the account
2. WHEN viewing a user details page, THE System SHALL display an embedded form to add the user to accounts
3. WHEN creating an account-user relationship, THE System SHALL validate that the relationship doesn't already exist
4. WHEN selecting a role, THE System SHALL provide options for agent and administrator roles
5. WHEN removing a relationship, THE System SHALL prevent removing the last administrator from an account
6. THE System SHALL show existing relationships with edit/remove capabilities

### Requirement 4: Navigation Simplification

**User Story:** As a SuperAdmin, I want a clean navigation interface that matches the Rails system, so that I have a familiar and uncluttered experience.

#### Acceptance Criteria

1. THE System SHALL remove "Access Tokens" and "Installation Configs" from the main navigation sidebar
2. THE System SHALL remove "Account Users" from the main navigation sidebar
3. THE System SHALL remove "Audit Logs" and "Cache" from the main navigation sidebar
4. THE System SHALL provide access to Installation Configs through a Settings menu or direct URL
5. THE System SHALL provide access to Access Tokens through direct URL only
6. THE System SHALL maintain existing navigation for Dashboard, Accounts, Users, Agent Bots, Platform Apps, and Settings

### Requirement 5: Settings Menu Integration

**User Story:** As a SuperAdmin, I want to access configuration pages through a settings menu, so that I can manage different configuration categories efficiently.

#### Acceptance Criteria

1. WHEN clicking the Settings menu, THE System SHALL show expandable configuration categories
2. WHEN selecting a configuration category, THE System SHALL navigate to the appropriate config page
3. THE System SHALL support configuration categories: General, Email, Facebook, Slack, Google, Microsoft, etc.
4. WHEN on a configuration page, THE System SHALL highlight the active category in the settings menu
5. THE System SHALL maintain the same URL pattern as Rails: `/app/super_admin/settings?config=category`

### Requirement 6: Data Consistency and Validation

**User Story:** As a SuperAdmin, I want data validation and error handling, so that I can safely manage system configurations and relationships.

#### Acceptance Criteria

1. WHEN saving invalid configuration data, THE System SHALL display specific validation errors
2. WHEN creating duplicate account-user relationships, THE System SHALL prevent creation and show error message
3. WHEN removing critical relationships, THE System SHALL validate business rules (e.g., last admin)
4. WHEN accessing non-existent resources, THE System SHALL show appropriate 404 or error pages
5. THE System SHALL maintain data consistency between frontend and backend validation
6. WHEN network errors occur, THE System SHALL show user-friendly error messages

### Requirement 7: Rails Functional Parity

**User Story:** As a SuperAdmin migrating from Rails, I want the same functionality and workflow, so that I can perform all administrative tasks without learning new patterns.

#### Acceptance Criteria

1. THE System SHALL provide the same configuration options available in Rails SuperAdmin
2. THE System SHALL maintain the same validation rules as Rails InstallationConfig model
3. THE System SHALL support the same access token filtering and management as Rails
4. THE System SHALL provide equivalent account-user relationship management as Rails embedded forms
5. THE System SHALL use the same URL patterns where possible for bookmarking compatibility
6. THE System SHALL maintain the same security restrictions (locked configs, token masking, etc.)