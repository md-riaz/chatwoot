# Draft Messages Feature

## Overview

The Draft Messages feature allows users to save draft messages for conversations that can be retrieved and edited later. Each user has their own draft per conversation, providing isolation between different agents working on the same conversation.

## Features

- **User-specific drafts**: Each user can have their own draft for a conversation
- **Auto-save support**: Frontend can implement auto-save by calling the update endpoint periodically
- **Conflict resolution**: Basic timestamp-based conflict detection to prevent overwriting newer drafts
- **Cache-based storage**: Uses Laravel's cache system for fast access and automatic expiration
- **7-day retention**: Drafts are automatically cleaned up after 7 days
- **Input validation**: Comprehensive validation with proper error messages
- **Logging**: Detailed logging for debugging and monitoring
- **Error handling**: Robust error handling with graceful degradation

## API Endpoints

### Get Draft Message
```
GET /api/v1/accounts/{account}/conversations/{conversation}/draft_messages
```

**Response:**
```json
{
  "has_draft": true,
  "message": "Draft message content",
  "updated_at": "2024-01-01T12:00:00.000000Z",
  "user_id": 123
}
```

### Save Draft Message
```
PATCH /api/v1/accounts/{account}/conversations/{conversation}/draft_messages
```

**Request:**
```json
{
  "draft_message": {
    "message": "Draft message content",
    "updated_at": "2024-01-01T12:00:00.000000Z" // Optional for conflict detection
  }
}
```

**Response:**
```json
{
  "message": "Draft saved successfully",
  "updated_at": "2024-01-01T12:00:00.000000Z"
}
```

### Delete Draft Message
```
DELETE /api/v1/accounts/{account}/conversations/{conversation}/draft_messages
```

**Response:** 204 No Content

## Implementation Details

### Architecture

- **Controller**: `DraftMessagesController` - Handles HTTP requests with comprehensive error handling
- **Action**: `ManageDraftMessageAction` - Contains business logic with logging and validation
- **Data**: `DraftMessageData` - Validates and structures request data with enhanced validation
- **Resource**: `DraftMessageResource` - Formats API responses
- **Command**: `CleanupDraftMessages` - Console command for maintenance

### Storage

Drafts are stored in Laravel's cache system with the following key pattern:
```
conversation_draft_message:{conversation_id}:user:{user_id}
```

### Validation

- Message content is required and limited to 10,000 characters
- Message cannot be empty or contain only whitespace
- Optional timestamp validation for conflict detection with ISO 8601 format
- Account access is verified through middleware
- Input is automatically trimmed

### Conflict Resolution

When a client provides an `updated_at` timestamp, the system checks if the stored draft has a newer timestamp. If so, a validation error is returned to prevent overwriting newer changes.

### Error Handling

- Comprehensive exception handling with logging
- Graceful degradation on cache failures
- Detailed error messages for validation failures
- Proper HTTP status codes

### Logging

The system logs:
- Draft save/delete operations with metadata
- Validation failures with context
- Cache operation failures
- Performance metrics

## Console Commands

### Cleanup Expired Drafts
```bash
php artisan drafts:cleanup
```

This command can be scheduled to run periodically for maintenance, though the cache TTL handles most cleanup automatically.

## Frontend Integration

### Auto-save Implementation

```javascript
// Example auto-save implementation
let autoSaveTimer;
const AUTOSAVE_DELAY = 2000; // 2 seconds

function onMessageChange(message) {
  clearTimeout(autoSaveTimer);
  autoSaveTimer = setTimeout(() => {
    saveDraft(message);
  }, AUTOSAVE_DELAY);
}

async function saveDraft(message) {
  try {
    const response = await fetch(`/api/v1/accounts/${accountId}/conversations/${conversationId}/draft_messages`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        draft_message: {
          message: message.trim(), // Trim whitespace
          updated_at: lastUpdatedAt // For conflict detection
        }
      })
    });
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    
    const data = await response.json();
    lastUpdatedAt = data.updated_at;
  } catch (error) {
    console.error('Failed to save draft:', error);
    handleSaveError(error);
  }
}
```

### Conflict Handling

```javascript
async function saveDraft(message) {
  try {
    // ... save request
  } catch (error) {
    if (error.status === 422) {
      const errorData = await error.response.json();
      if (errorData.errors?.['draft_message.updated_at']) {
        // Handle conflict - show user a message about newer changes
        showConflictDialog();
      }
    }
  }
}
```

## Testing

The feature includes comprehensive tests:

- **Unit tests**: `ManageDraftMessageActionTest.php` - Tests the business logic
- **Feature tests**: `DraftMessagesControllerTest.php` - Tests the API endpoints
- **Coverage**: User isolation, conflict detection, validation, authorization, error handling

## Security

- Authentication required via Sanctum
- Account access verified via middleware
- User-specific draft isolation
- Input validation and sanitization
- Rate limiting through Laravel's default throttling
- Comprehensive logging for audit trails

## Performance

- Cache-based storage for fast access
- Automatic expiration (7 days) prevents cache bloat
- Minimal database impact (no database storage)
- Efficient key structure for quick lookups
- Optimized for high-frequency auto-save operations

## Monitoring

The system provides monitoring capabilities:
- Draft statistics via `getDraftStats()` method
- Comprehensive logging for operations
- Error tracking and alerting
- Performance metrics collection

## Maintenance

- Automatic cleanup via cache TTL
- Manual cleanup command available
- Comprehensive error logging
- Health check capabilities