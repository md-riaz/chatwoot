# Implementation Plan: SuperAdmin Missing Pages

## Overview

This implementation plan creates the missing SuperAdmin pages in SvelteKit to achieve functional parity with the Rails system. The approach focuses on enhancing existing pages and creating simple utility pages rather than complex new features.

## Task Execution Instructions

**CRITICAL**: For every task, follow this workflow:

1. **Scan Current Implementation** - Examine existing SvelteKit SuperAdmin pages and components
2. **Check Rails System Parity** - Analyze corresponding Rails views, controllers, and functionality  
3. **Validate Task Legitimacy** - Ensure the task addresses a real gap between systems
4. **Implement Changes** - Only proceed with implementation after confirming the need

This ensures each task is necessary and maintains accurate Rails functional parity.

## Tasks

- [ ] 1. Update SuperAdmin navigation layout
  - Remove "Access Tokens", "Installation Configs", "Account Users", "Audit Logs", and "Cache" from main navigation
  - Update navigation to match Rails exclusion patterns
  - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [x] 2. Create Settings page with expandable configuration categories
  - [x] 2.1 Create settings page component with category navigation
    - Implement expandable settings menu matching Rails structure
    - Support URL pattern `/app/super_admin/settings?config=category`
    - _Requirements: 1.8, 1.10, 1.11_

  - [ ]* 2.2 Write property test for settings navigation
    - **Property 4: URL pattern consistency**
    - **Validates: Requirements 1.11**

  - [x] 2.3 Implement configuration form with dynamic field types
    - Support boolean, text, secret, code, and select input types
    - Display configuration descriptions and help text
    - Handle locked configuration indicators
    - _Requirements: 1.1, 1.3, 1.5, 1.6_

  - [ ]* 2.4 Write property test for configuration validation
    - **Property 2: Configuration validation by type**
    - **Validates: Requirements 1.3, 5.1**

  - [ ]* 2.5 Write property test for locked configuration protection
    - **Property 3: Locked configuration protection**
    - **Validates: Requirements 1.4, 1.6**

- [ ] 3. Implement configuration category management
  - [ ] 3.1 Create configuration API client methods
    - Implement methods for fetching, updating, and validating configurations
    - Support category-based configuration loading
    - _Requirements: 1.2, 1.7, 1.9_

  - [ ]* 3.2 Write property test for configuration categorization
    - **Property 1: Configuration categorization and display**
    - **Validates: Requirements 1.2, 1.5, 1.7**

  - [ ] 3.3 Add configuration validation and error handling
    - Implement frontend validation matching backend rules
    - Display user-friendly error messages for validation failures
    - _Requirements: 5.1, 5.5_

  - [ ]* 3.4 Write property test for validation parity
    - **Property 11: Frontend-backend validation parity**
    - **Validates: Requirements 5.5**

- [ ] 4. Checkpoint - Ensure configuration management works
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Enhance User show page with access token display
  - [ ] 5.1 Add access token section to user detail page
    - Display user's access token information with required fields
    - Implement token masking for security
    - Add token revocation functionality
    - _Requirements: 2.1, 2.4, 2.7_

  - [ ]* 5.2 Write property test for token display on user pages
    - **Property 5: Access token display on owner pages**
    - **Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5**

  - [ ]* 5.3 Write property test for token security masking
    - **Property 6: Token security masking**
    - **Validates: Requirements 2.7**

- [ ] 6. Enhance AgentBot show page with access token display
  - [ ] 6.1 Add access token section to agent bot detail page
    - Display bot's access token information
    - Implement same token management features as user page
    - _Requirements: 2.2, 2.4, 2.7_

  - [ ]* 6.2 Write unit tests for bot token display
    - Test token information rendering and interaction
    - _Requirements: 2.2_

- [ ] 7. Enhance PlatformApp show page with access token display
  - [ ] 7.1 Add access token section to platform app detail page
    - Display app's access token information
    - Implement same token management features as other pages
    - _Requirements: 2.3, 2.4, 2.7_

  - [ ]* 7.2 Write property test for token revocation workflow
    - **Property 7: Token revocation workflow**
    - **Validates: Requirements 2.6**

- [ ] 8. Create system-wide access tokens utility page
  - [ ] 8.1 Create access tokens list page
    - Display all access tokens with owner information
    - Support filtering by owner type
    - Provide links back to owner pages
    - _Requirements: 2.8_

  - [ ]* 8.2 Write unit tests for access tokens utility page
    - Test token listing, filtering, and navigation
    - _Requirements: 2.8_

- [ ] 9. Add embedded account-user forms to account pages
  - [ ] 9.1 Create account-user relationship form component
    - Implement form for adding users to accounts
    - Support role selection (agent/administrator)
    - Add validation for duplicate relationships
    - _Requirements: 3.1, 3.3, 3.4_

  - [ ]* 9.2 Write property test for relationship validation
    - **Property 8: Account-user relationship validation**
    - **Validates: Requirements 3.3, 3.5, 5.2, 5.3**

  - [ ] 9.3 Add embedded form to account detail pages
    - Integrate account-user form into account show pages
    - Display existing relationships with edit/remove capabilities
    - _Requirements: 3.6_

  - [ ]* 9.4 Write property test for embedded form presence
    - **Property 9: Embedded form presence**
    - **Validates: Requirements 3.6**

- [ ] 10. Add embedded account-user forms to user pages
  - [ ] 10.1 Add embedded form to user detail pages
    - Integrate account-user form into user show pages
    - Support adding user to multiple accounts
    - _Requirements: 3.2, 3.6_

  - [ ]* 10.2 Write unit tests for user account forms
    - Test form rendering and submission
    - _Requirements: 3.2_

- [ ] 11. Implement comprehensive error handling
  - [ ] 11.1 Add error handling for configuration operations
    - Handle network errors, validation failures, and locked configs
    - Display user-friendly error messages with recovery options
    - _Requirements: 5.4, 5.6_

  - [ ]* 11.2 Write property test for error handling consistency
    - **Property 10: Error handling consistency**
    - **Validates: Requirements 5.4, 5.6**

  - [ ] 11.3 Add error handling for access token operations
    - Handle token not found, revocation failures, and permission errors
    - Provide clear error messages and navigation options
    - _Requirements: 5.4, 5.6_

  - [ ]* 11.4 Write unit tests for error scenarios
    - Test various error conditions and user feedback
    - _Requirements: 5.4, 5.6_

- [ ] 12. Final integration and Rails parity validation
  - [ ] 12.1 Test complete workflow integration
    - Verify all pages work together seamlessly
    - Test navigation flows and data consistency
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [ ]* 12.2 Write property test for Rails functional equivalence
    - **Property 12: Rails functional equivalence**
    - **Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5, 6.6**

  - [ ] 12.3 Perform final testing and validation
    - Run all tests and ensure functionality matches Rails system
    - Verify URL patterns, navigation, and user workflows
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

- [ ] 13. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- The implementation enhances existing pages rather than creating entirely new features
- Laravel API controllers already exist and don't require modification