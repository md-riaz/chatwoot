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
2. WHEN viewing configurations, THE System SHALL group configs by category (general, facebook, email, slack, google, microsoft, linear, notion, shopify, instagram, tiktok, whatsapp_embedded)
3. WHEN editing a configuration, THE System SHALL validate the input based on config type:
   - **boolean**: True/False dropdown selection
   - **text**: Standard text input field
   - **secret**: Password field with masked display
   - **code**: Multi-line textarea for JSON/code content
   - **select**: Dropdown with predefined options
4. WHEN saving configurations, THE System SHALL update only unlocked configs and show validation errors for locked configs
5. THE System SHALL display config descriptions and help text for each field
6. WHEN a config is locked, THE System SHALL prevent editing and show locked status indicator
7. THE System SHALL support all configuration fields from installation_config.yml (200+ fields across all categories)
8. WHEN clicking the Settings menu, THE System SHALL show expandable configuration categories
9. WHEN selecting a configuration category, THE System SHALL navigate to the appropriate config page with these exact categories:
   - **general**: ENABLE_ACCOUNT_SIGNUP, FIREBASE_PROJECT_ID, FIREBASE_CREDENTIALS, WEBHOOK_TIMEOUT, MAXIMUM_FILE_UPLOAD_SIZE
   - **facebook**: FB_APP_ID, FB_VERIFY_TOKEN, FB_APP_SECRET, IG_VERIFY_TOKEN, FACEBOOK_API_VERSION, ENABLE_MESSENGER_CHANNEL_HUMAN_AGENT
   - **shopify**: SHOPIFY_CLIENT_ID, SHOPIFY_CLIENT_SECRET
   - **microsoft**: AZURE_APP_ID, AZURE_APP_SECRET
   - **email**: MAILER_INBOUND_EMAIL_DOMAIN
   - **linear**: LINEAR_CLIENT_ID, LINEAR_CLIENT_SECRET
   - **slack**: SLACK_CLIENT_ID, SLACK_CLIENT_SECRET
   - **instagram**: INSTAGRAM_APP_ID, INSTAGRAM_APP_SECRET, INSTAGRAM_VERIFY_TOKEN, INSTAGRAM_API_VERSION, ENABLE_INSTAGRAM_CHANNEL_HUMAN_AGENT
   - **tiktok**: TIKTOK_APP_ID, TIKTOK_APP_SECRET
   - **whatsapp_embedded**: WHATSAPP_APP_ID, WHATSAPP_APP_SECRET, WHATSAPP_CONFIGURATION_ID, WHATSAPP_API_VERSION
   - **notion**: NOTION_CLIENT_ID, NOTION_CLIENT_SECRET
   - **google**: GOOGLE_OAUTH_CLIENT_ID, GOOGLE_OAUTH_CLIENT_SECRET, GOOGLE_OAUTH_REDIRECT_URI, ENABLE_GOOGLE_OAUTH_LOGIN
10. WHEN on a configuration page, THE System SHALL highlight the active category in the settings menu
11. THE System SHALL maintain the same URL pattern as Rails: `/app/super_admin/settings?config=category`

### Requirement 2: Access Tokens Management

**User Story:** As a SuperAdmin, I want to view and manage API access tokens from their owner pages, so that I can monitor and control API access security in context of the owning entity.

#### Acceptance Criteria

1. WHEN viewing a User show page, THE System SHALL display the user's access token information
2. WHEN viewing an AgentBot show page, THE System SHALL display the bot's access token information  
3. WHEN viewing a PlatformApp show page, THE System SHALL display the app's access token information
4. WHEN displaying access tokens, THE System SHALL show token name, creation date, and last used date
5. WHEN viewing token details, THE System SHALL show abilities and expiration information
6. WHEN revoking a token, THE System SHALL delete the token and show confirmation
7. THE System SHALL mask token values for security (show only partial token)
8. THE System SHALL provide a link to view all access tokens at `/app/super_admin/access-tokens` for system-wide management
9. THE System SHALL NOT display Access Tokens in the main navigation sidebar (accessed via owner pages or direct URL)

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

1. THE System SHALL remove "Access Tokens" from the main navigation sidebar (accessed via User/AgentBot/PlatformApp pages)
2. THE System SHALL remove "Installation Configs" from the main navigation sidebar (accessed via Settings menu)
3. THE System SHALL remove "Account Users" from the main navigation sidebar (embedded forms only)
4. THE System SHALL remove "Audit Logs" and "Cache" from the main navigation sidebar (don't exist in Rails)
5. THE System SHALL provide access to Installation Configs through Settings menu (matches Rails pattern)
6. THE System SHALL provide access to Access Tokens through owner entity pages and optional direct URL
7. THE System SHALL maintain existing navigation for Dashboard, Accounts, Users, Agent Bots, Platform Apps, and Settings

### Requirement 5: Data Consistency and Validation

**User Story:** As a SuperAdmin, I want data validation and error handling, so that I can safely manage system configurations and relationships.

#### Acceptance Criteria

1. WHEN saving invalid configuration data, THE System SHALL display specific validation errors
2. WHEN creating duplicate account-user relationships, THE System SHALL prevent creation and show error message
3. WHEN removing critical relationships, THE System SHALL validate business rules (e.g., last admin)
4. WHEN accessing non-existent resources, THE System SHALL show appropriate 404 or error pages
5. THE System SHALL maintain data consistency between frontend and backend validation
6. WHEN network errors occur, THE System SHALL show user-friendly error messages

### Requirement 6: Rails Functional Parity

**User Story:** As a SuperAdmin migrating from Rails, I want the same functionality and workflow, so that I can perform all administrative tasks without learning new patterns.

#### Acceptance Criteria

1. THE System SHALL provide the same configuration options available in Rails SuperAdmin
2. THE System SHALL maintain the same validation rules as Rails InstallationConfig model
3. THE System SHALL support the same access token filtering and management as Rails
4. THE System SHALL provide equivalent account-user relationship management as Rails embedded forms
5. THE System SHALL use the same URL patterns where possible for bookmarking compatibility
6. THE System SHALL maintain the same security restrictions (locked configs, token masking, etc.)