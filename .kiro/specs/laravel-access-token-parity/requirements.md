# Requirements Document

## Introduction

This document specifies the requirements for implementing functional parity between the Rails backend access token system and the Laravel API. The goal is to achieve the same functionality as Rails but implemented using Laravel best practices and conventions.

**Key Principle:** Functional parity with Rails backend, but implementation follows Laravel patterns (middleware instead of concerns, traits instead of modules, Laravel's built-in features where applicable). The API behavior and responses should match Rails, but the internal implementation uses idiomatic Laravel code.

The Rails backend has a polymorphic access token system that automatically generates tokens for models (User, AgentBot, PlatformApp) and provides API authentication via the `api_access_token` header.

## Glossary

- **Access_Token**: A secure, randomly generated string used to authenticate API requests
- **Owner**: The polymorphic entity (User, AgentBot, or PlatformApp) that owns an access token
- **AccessTokenable**: A trait/concern that provides automatic access token generation for models
- **Bot_Accessible_Endpoints**: Specific API endpoints that AgentBots are permitted to access
- **Platform_App**: An external application that integrates with the system via API
- **Agent_Bot**: An automated bot that can interact with conversations via API

## Requirements

### Requirement 1: Polymorphic Access Token Model

**User Story:** As a developer, I want a polymorphic access token model, so that any model type can have an associated access token for API authentication.

#### Acceptance Criteria

1. THE Access_Token model SHALL have a polymorphic `owner` relationship supporting User, AgentBot, and PlatformApp types
2. WHEN an Access_Token is created without a token value, THE system SHALL automatically generate a secure random token (using `has_secure_token` equivalent)
3. THE Access_Token model SHALL provide a `regenerate_token()` method that generates a new token and persists it

### Requirement 2: AccessTokenable Trait for Models

**User Story:** As a developer, I want models to automatically receive access tokens upon creation, so that API authentication is seamlessly available for new entities.

#### Acceptance Criteria

1. WHEN a model using the AccessTokenable trait is created, THE system SHALL automatically create an associated Access_Token (after_create callback)
2. THE AccessTokenable trait SHALL provide a `has_one` relationship to Access_Token as owner with dependent destroy
3. THE AccessTokenable trait SHALL provide a `create_access_token()` method for manual token creation
4. THE User model SHALL use the AccessTokenable trait
5. THE AgentBot model SHALL use the AccessTokenable trait
6. THE PlatformApp model SHALL use the AccessTokenable trait

### Requirement 3: Access Token API Authentication

**User Story:** As an API consumer, I want to authenticate requests using an access token header, so that I can access protected endpoints without session-based authentication.

#### Acceptance Criteria

1. WHEN a request includes the `api_access_token` header, THE system SHALL look up the Access_Token by token value
2. WHEN a request includes the `HTTP_API_ACCESS_TOKEN` header, THE system SHALL look up the Access_Token by token value
3. WHEN a valid access token is found, THE system SHALL set the resource to the token's owner
4. WHEN a valid access token belongs to a User or AgentBot, THE system SHALL set Current.user to the owner
5. WHEN an invalid or missing access token is provided for protected routes, THE system SHALL return a 401 Unauthorized response with message "Invalid Access Token"
6. IF no access token header is present, THEN THE system SHALL fall back to standard user authentication

### Requirement 4: Bot Access Endpoint Restrictions

**User Story:** As a system administrator, I want to restrict which API endpoints bots can access, so that automated agents have limited permissions appropriate to their function.

#### Acceptance Criteria

1. THE system SHALL define BOT_ACCESSIBLE_ENDPOINTS as a constant mapping controllers to allowed actions
2. THE bot-accessible endpoints SHALL include `api/v1/accounts/conversations` with actions: toggle_status, toggle_priority, create, update, custom_attributes
3. THE bot-accessible endpoints SHALL include `api/v1/accounts/conversations/messages` with action: create
4. THE bot-accessible endpoints SHALL include `api/v1/accounts/conversations/assignments` with action: create
5. WHEN an AgentBot attempts to access a non-permitted endpoint, THE system SHALL return a 401 Unauthorized response with message "Access to this endpoint is not authorized for bots"
6. WHEN a User access token is used, THE system SHALL skip bot endpoint validation

### Requirement 5: Platform App Authentication

**User Story:** As a platform app developer, I want to authenticate my application using an access token, so that I can access platform-level API endpoints.

#### Acceptance Criteria

1. WHEN a PlatformApp access token is provided to platform routes, THE system SHALL authenticate the request as the platform app
2. WHEN the access token owner is not a PlatformApp, THE system SHALL return a 401 Unauthorized response with message "Invalid access_token"
3. WHEN a platform app attempts to access a resource, THE system SHALL validate the resource is in platform_app_permissibles
4. WHEN a platform app attempts to access a non-permissible resource, THE system SHALL return a 401 Unauthorized response with message "Non permissible resource"

### Requirement 6: Profile Access Token Reset

**User Story:** As a user, I want to reset my access token through my profile, so that I can invalidate old tokens and get a new one for security purposes.

#### Acceptance Criteria

1. THE Profile API SHALL provide a `reset_access_token` POST endpoint
2. WHEN a user calls reset_access_token, THE system SHALL call regenerate_token on the user's access token
3. WHEN a user resets their access token, THE response SHALL include the updated user with new token

### Requirement 7: Agent Bot Access Token Reset

**User Story:** As an account administrator, I want to reset agent bot access tokens, so that I can maintain security for automated integrations.

#### Acceptance Criteria

1. THE AgentBot API SHALL provide a `reset_access_token` POST endpoint at `/api/v1/accounts/{account_id}/agent_bots/{id}/reset_access_token`
2. WHEN an admin calls reset_access_token, THE system SHALL call regenerate_token on the bot's access token
3. WHEN an admin resets a bot's access token, THE response SHALL include the updated bot with new token
4. THE reset_access_token action SHALL require administrator role

### Requirement 8: SuperAdmin Access Token Management

**User Story:** As a super admin, I want to view and manage access tokens through the admin interface, so that I can monitor and control API access.

#### Acceptance Criteria

1. THE SuperAdmin SHALL be able to list all access tokens with owner information
2. THE SuperAdmin SHALL be able to filter access tokens by owner_type (User, AgentBot, PlatformApp)
3. THE SuperAdmin SHALL be able to view individual access token details
4. THE SuperAdmin SHALL be able to delete access tokens
