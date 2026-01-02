# Conversation Participants Implementation

## Overview

This document describes the implementation of the Conversation Participants feature in Laravel, which provides 100% functional parity with the Rails backend while following proper Laravel architecture patterns as defined in AGENTS.md.

## Architecture Pattern: Action → Repository → Model

Following the AGENTS.md guidelines, this implementation uses the recommended Laravel architecture:

- **Controller**: Thin layer that validates input, calls Action, returns Resource
- **Action**: Business logic encapsulation using lorisleiva/laravel-actions
- **Repository**: Data access layer for database operations
- **Data**: Spatie Data objects for typed request/response payloads
- **Model**: Eloquent model with relationships and validations

## Components Implemented

### 1. Action: `ManageParticipantsAction`
- **Location**: `app/Actions/Conversation/ManageParticipantsAction.php`
- **Pattern**: Uses `lorisleiva/laravel-actions` for business logic
- **Features**:
  - Encapsulates all participant management business logic
  - Handles validation for inbox access
  - Transaction management for data consistency
  - Implements Rails logic patterns (add/remove/update participants)

### 2. Repository: `ParticipantRepository`
- **Location**: `app/Repositories/Conversation/ParticipantRepository.php`
- **Pattern**: Extends BaseRepository for consistent data access
- **Features**:
  - Database operations abstraction
  - Query optimization and reusability
  - Separation of data access from business logic

### 3. Data Object: `ParticipantData`
- **Location**: `app/Data/Conversation/ParticipantData.php`
- **Pattern**: Uses Spatie Data for typed request validation
- **Features**:
  - Type-safe request payload handling
  - Automatic validation rules
  - Better IDE support and documentation

### 4. Model: `ConversationParticipant`
- **Location**: `app/Models/ConversationParticipant.php`
- **Features**:
  - Automatic `account_id` assignment from conversation
  - Validation for required fields (account_id, conversation_id, user_id)
  - Uniqueness validation (user_id scoped to conversation_id)
  - Inbox access validation (ensures user has access to conversation's inbox)
  - Relationships: belongs to Account, Conversation, and User

### 5. Controller: `ParticipantsController`
- **Location**: `app/Http/Controllers/Api/V1/Conversations/ParticipantsController.php`
- **Pattern**: Thin controller following AGENTS.md guidelines
- **Features**:
  - Minimal logic: validate input → call Action → return Resource
  - Uses Data objects for request validation
  - Uses Actions for business logic
  - Returns UserResource for consistent API responses
- **Endpoints**:
  - `GET /api/v1/accounts/{account}/conversations/{conversation}/participants` - List participants
  - `POST /api/v1/accounts/{account}/conversations/{conversation}/participants` - Add participants
  - `PATCH /api/v1/accounts/{account}/conversations/{conversation}/participants` - Update participants
  - `DELETE /api/v1/accounts/{account}/conversations/{conversation}/participants` - Remove participants

### 6. Database Migration
- **Location**: `database/migrations/2024_01_01_000026_create_conversation_participants_table.php`
- **Features**:
  - Complete table structure matching Rails schema
  - All indexes from Rails implementation
  - Foreign key constraints for data integrity
  - Unique constraint on user_id + conversation_id

### 7. Factory
- **Location**: `database/factories/ConversationParticipantFactory.php`
- **Features**: Test data generation for participants

### 8. Tests
- **Feature Tests**: `tests/Feature/Api/Conversations/Participants/ParticipantsTest.php`
- **Unit Tests**: 
  - `tests/Unit/Models/ConversationParticipantTest.php`
  - `tests/Unit/Services/ParticipantServiceTest.php` (deprecated - replaced with Action tests)

### 9. OpenAPI Documentation
- **Location**: `docs/openapi/paths/conversations.yaml`
- **Features**:
  - Complete OpenAPI 3.0 specification for all participants endpoints
  - Request/response schemas and examples
  - Error response documentation
  - Authentication requirements
- **Additional Documentation**: `docs/openapi/PARTICIPANTS_API_DOCUMENTATION.md`
  - Comprehensive API usage guide
  - cURL and JavaScript examples
  - Business rules and error handling
  - Implementation notes

## Rails Parity Features

### ✅ Model Validations
- [x] Required field validation (account_id, conversation_id, user_id)
- [x] Uniqueness validation (user_id scoped to conversation_id)
- [x] Inbox access validation (ensure_inbox_access equivalent)
- [x] Automatic account_id assignment (ensure_account_id equivalent)

### ✅ Controller Methods
- [x] `show` - List participants (returns user data like Rails)
- [x] `create` - Add participants (matches Rails method name)
- [x] `update` - Replace participants (implements Rails add/remove logic)
- [x] `destroy` - Remove participants (returns 200 OK like Rails)

### ✅ Business Logic
- [x] `participants_to_be_added_ids` logic
- [x] `participants_to_be_removed_ids` logic
- [x] `current_participant_ids` logic
- [x] Transaction handling for data consistency
- [x] Proper error handling and validation

### ✅ Response Format
- [x] Returns user data (not participant objects) like Rails
- [x] Uses UserResource for consistent formatting
- [x] Proper HTTP status codes matching Rails

### ✅ Database Schema
- [x] All indexes from Rails schema
- [x] Proper foreign key constraints
- [x] Unique constraint on user_id + conversation_id
- [x] Timestamps and proper column types

## API Usage Examples

### List Participants
```bash
GET /api/v1/accounts/1/conversations/123/participants
```

### Add Participants
```bash
POST /api/v1/accounts/1/conversations/123/participants
Content-Type: application/json

{
  "user_ids": [456, 789]
}
```

### Update Participants (Replace)
```bash
PATCH /api/v1/accounts/1/conversations/123/participants
Content-Type: application/json

{
  "user_ids": [456, 999]  // Replaces existing participants
}
```

### Remove Participants
```bash
DELETE /api/v1/accounts/1/conversations/123/participants
Content-Type: application/json

{
  "user_ids": [456]
}
```

## Key Differences from Rails

1. **Enhanced Validation**: Laravel implementation includes more comprehensive validation in the model
2. **Foreign Key Constraints**: Laravel uses proper foreign key constraints for data integrity
3. **Service Layer**: Added dedicated service class for better separation of concerns
4. **Resource Classes**: Uses Laravel resource classes for consistent API responses
5. **Better Error Handling**: More detailed validation error messages

## Testing

The implementation includes comprehensive tests:
- Feature tests for all API endpoints
- Unit tests for model validations
- Unit tests for service layer functionality
- Authorization and security tests

Run tests with:
```bash
php artisan test tests/Feature/Api/Conversations/Participants/
php artisan test tests/Unit/Models/ConversationParticipantTest.php
php artisan test tests/Unit/Services/ParticipantServiceTest.php
```

## Status: ✅ COMPLETE

This implementation provides 100% functional parity with the Rails backend conversation participants feature, with additional improvements for data integrity and error handling.