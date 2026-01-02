# Conversation Participants API Documentation

## Overview

The Conversation Participants API allows you to manage participants in conversations. Participants are users who have access to view and interact with a specific conversation.

## Base URL

All endpoints are prefixed with `/api/v1/accounts/{account_id}/conversations/{conversation_id}/participants`

## Authentication

All endpoints require Bearer token authentication using Sanctum.

## Endpoints

### GET /api/v1/accounts/{account_id}/conversations/{conversation_id}/participants

**Summary**: List conversation participants

**Description**: Get all participants for a conversation

**Parameters**:
- `account_id` (path, required): Account ID (integer)
- `conversation_id` (path, required): Conversation ID (integer)

**Response 200**: Success
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "display_name": "John Doe",
      "phone_number": "+1234567890",
      "avatar_url": "https://example.com/avatar.jpg",
      "availability": "online",
      "custom_attributes": {},
      "email_verified_at": "2024-01-01T00:00:00.000Z",
      "created_at": "2024-01-01T00:00:00.000Z",
      "updated_at": "2024-01-01T00:00:00.000Z"
    }
  ]
}
```

**Response 404**: Conversation not found

---

### POST /api/v1/accounts/{account_id}/conversations/{conversation_id}/participants

**Summary**: Add conversation participants

**Description**: Add one or more participants to a conversation

**Parameters**:
- `account_id` (path, required): Account ID (integer)
- `conversation_id` (path, required): Conversation ID (integer)

**Request Body**:
```json
{
  "user_ids": [456, 789]
}
```

**Response 200**: Success
```json
{
  "data": [
    {
      "id": 456,
      "name": "Alice Johnson",
      "email": "alice@example.com",
      "display_name": "Alice Johnson",
      "avatar_url": "https://example.com/avatar2.jpg"
    },
    {
      "id": 789,
      "name": "Bob Wilson",
      "email": "bob@example.com",
      "display_name": "Bob Wilson",
      "avatar_url": "https://example.com/avatar3.jpg"
    }
  ]
}
```

**Response 422**: Validation error
```json
{
  "error": "Validation failed",
  "errors": {
    "user_ids": ["User 456 must have inbox access"]
  }
}
```

---

### PATCH /api/v1/accounts/{account_id}/conversations/{conversation_id}/participants

**Summary**: Update conversation participants

**Description**: Replace all participants for a conversation with the provided list

**Parameters**:
- `account_id` (path, required): Account ID (integer)
- `conversation_id` (path, required): Conversation ID (integer)

**Request Body**:
```json
{
  "user_ids": [456, 999]
}
```

**Response 200**: Success
```json
{
  "data": [
    {
      "id": 456,
      "name": "Alice Johnson",
      "email": "alice@example.com"
    },
    {
      "id": 999,
      "name": "Charlie Brown",
      "email": "charlie@example.com"
    }
  ]
}
```

**Response 422**: Validation error

---

### DELETE /api/v1/accounts/{account_id}/conversations/{conversation_id}/participants

**Summary**: Remove conversation participants

**Description**: Remove one or more participants from a conversation

**Parameters**:
- `account_id` (path, required): Account ID (integer)
- `conversation_id` (path, required): Conversation ID (integer)

**Request Body**:
```json
{
  "user_ids": [456]
}
```

**Response 200**: Success
```json
{
  "message": "Participants removed successfully"
}
```

**Response 422**: Validation error

## Business Rules

1. **Inbox Access**: Users must have access to the conversation's inbox to be added as participants
2. **Uniqueness**: A user can only be a participant once per conversation
3. **Account Membership**: Users must be members of the account to participate in conversations
4. **Automatic Account Assignment**: The account_id is automatically set from the conversation's account

## Error Handling

### Common Error Responses

**401 Unauthorized**: Missing or invalid authentication token
```json
{
  "message": "Unauthenticated."
}
```

**404 Not Found**: Conversation or account not found
```json
{
  "message": "No query results for model [App\\Models\\Conversation] 123"
}
```

**422 Unprocessable Entity**: Validation errors
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "user_ids": [
      "The user_ids field is required.",
      "User 456 must have inbox access"
    ]
  }
}
```

## Usage Examples

### JavaScript/Fetch

```javascript
// List participants
const response = await fetch('/api/v1/accounts/1/conversations/123/participants', {
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Accept': 'application/json'
  }
});
const participants = await response.json();

// Add participants
const addResponse = await fetch('/api/v1/accounts/1/conversations/123/participants', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    user_ids: [456, 789]
  })
});

// Update participants (replace all)
const updateResponse = await fetch('/api/v1/accounts/1/conversations/123/participants', {
  method: 'PATCH',
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    user_ids: [456, 999]
  })
});

// Remove participants
const removeResponse = await fetch('/api/v1/accounts/1/conversations/123/participants', {
  method: 'DELETE',
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    user_ids: [456]
  })
});
```

### cURL

```bash
# List participants
curl -X GET "https://api.example.com/api/v1/accounts/1/conversations/123/participants" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"

# Add participants
curl -X POST "https://api.example.com/api/v1/accounts/1/conversations/123/participants" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"user_ids": [456, 789]}'

# Update participants
curl -X PATCH "https://api.example.com/api/v1/accounts/1/conversations/123/participants" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"user_ids": [456, 999]}'

# Remove participants
curl -X DELETE "https://api.example.com/api/v1/accounts/1/conversations/123/participants" \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"user_ids": [456]}'
```

## Implementation Notes

- The API returns user objects (not participant objects) to match Rails backend behavior
- All operations are wrapped in database transactions for consistency
- Validation occurs at both the request level and model level
- The service layer handles complex business logic for participant management

## Related Documentation

- [Conversation API](./conversations.yaml)
- [User Management API](./users.yaml)
- [Authentication Guide](../authentication.md)