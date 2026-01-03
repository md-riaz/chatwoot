# Draft Messages Feature

## Overview

The Draft Messages feature allows users to save draft messages for conversations that can be retrieved and edited later. Each user has their own draft per conversation, providing isolation between different agents working on the same conversation.

## Features

- **User-specific drafts**: Each user can have their own draft for a conversation
- **Auto-save support**: Frontend can implement auto-save by calling the update endpoint periodically
- **Conflict resolution**: Basic timestamp-based conflict detection to prevent overwriting newer drafts
- **Cache-based storage**: Uses Laravel's cache system for fast access and automatic expiration
- **7-day retention**: Drafts are automatically cleaned up after 7 days

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

- **Controller**: `DraftMessagesController` - Handles HTTP requests
- **Action**: `ManageDraftMessageAction` - Contains business logic
- **Data**: `DraftMessageData` - Validates and structures request data
- **Resource**: `DraftMessageResource` - Formats API responses

### Storage

Drafts are stored in Laravel's cache system with the following key pattern:
```
conversation_draft_message:{conversation_id}:user:{user_id}
```

### Validation

- Message content is required and limited to 10,000 characters
- Optional timestamp validation for conflict detection
- Account access is verified through middleware

### Conflict Resolution

When a client provides an `updated_at` timestamp, the system checks if the stored draft has a newer timestamp. If so, a validation error is returned to prevent overwriting newer changes.

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
          message: message,
          updated_at: lastUpdatedAt // For conflict detection
        }
      })
    });
    
    const data = await response.json();
    lastUpdatedAt = data.updated_at;
  } catch (error) {
    console.error('Failed to save draft:', error);
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
      // Handle conflict - show user a message about newer changes
      showConflictDialog();
    }
  }
}
```

## Testing

The feature includes comprehensive tests:

- **Unit tests**: `ManageDraftMessageActionTest.php` - Tests the business logic
- **Feature tests**: `DraftMessagesTest.php` - Tests the API endpoints
- **Coverage**: User isolation, conflict detection, validation, authorization

## Security

- Authentication required via Sanctum
- Account access verified via middleware
- User-specific draft isolation
- Input validation and sanitization
- Rate limiting through Laravel's default throttling

## Performance

- Cache-based storage for fast access
- Automatic expiration (7 days) prevents cache bloat
- Minimal database impact (no database storage)
- Efficient key structure for quick lookups