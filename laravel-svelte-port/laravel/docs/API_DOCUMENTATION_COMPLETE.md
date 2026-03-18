# ClearLine Laravel API Documentation - Complete Reference

This document provides comprehensive documentation for all API endpoints in the ClearLine Laravel application, including deployment procedures, configuration management, and troubleshooting guides.

## Table of Contents

1. [API Overview](#api-overview)
2. [Authentication Methods](#authentication-methods)
3. [Core API Endpoints](#core-api-endpoints)
4. [Channel Integration APIs](#channel-integration-apis)
5. [Third-Party Integration APIs](#third-party-integration-apis)
6. [Super Admin APIs](#super-admin-apis)
7. [Widget and Public APIs](#widget-and-public-apis)
8. [WebSocket and Real-time APIs](#websocket-and-real-time-apis)
9. [Error Handling](#error-handling)
10. [Rate Limiting](#rate-limiting)
11. [API Versioning](#api-versioning)
12. [SDK and Client Libraries](#sdk-and-client-libraries)

## API Overview

ClearLine Laravel provides a comprehensive REST API with over 350 endpoints covering:

- **Core Resources**: Accounts, Users, Conversations, Messages, Contacts
- **Channel Integrations**: WhatsApp, Facebook, Email, SMS, Telegram, Twitter, LINE
- **Third-Party Integrations**: Slack, Linear, Shopify, OpenAI, Dialogflow
- **Enterprise Features**: SAML SSO, SLA Policies, Custom Roles, Audit Logs
- **Real-time Features**: WebSocket broadcasting, Live chat, Presence tracking
- **Super Admin**: Platform-wide administration and management

### Base URL

All API endpoints are prefixed with `/api/v1/`:

```
Production: https://your-domain.com/api/v1/
Development: http://localhost:8000/api/v1/
```

### Response Format

All API responses follow a consistent JSON format:

```json
{
  "data": {
    // Response data
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 25,
      "total": 100,
      "last_page": 4
    }
  }
}
```

### Error Response Format

```json
{
  "error": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  },
  "code": "ERROR_CODE",
  "status": 422
}
```

## Authentication Methods

### 1. Bearer Token Authentication (Sanctum)

Most authenticated endpoints use Laravel Sanctum Bearer tokens:

```bash
curl -X GET "https://api.example.com/api/v1/profile" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**Token Generation:**
```bash
# Login to get token
curl -X POST "https://api.example.com/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

### 2. Platform API Key Authentication

Platform-level endpoints use API key authentication:

```bash
curl -X GET "https://api.example.com/api/v1/platform/users/1" \
  -H "api_access_token: YOUR_PLATFORM_API_KEY" \
  -H "Accept: application/json"
```

### 3. Widget Token Authentication

Widget endpoints use X-Auth-Token header:

```bash
curl -X GET "https://api.example.com/api/v1/widget/contact" \
  -H "X-Auth-Token: WIDGET_AUTH_TOKEN" \
  -H "Accept: application/json"
```

### 4. Public Endpoints

Some endpoints require no authentication:

```bash
curl -X POST "https://api.example.com/api/v1/widget/config" \
  -H "Content-Type: application/json" \
  -d '{"website_token": "YOUR_WEBSITE_TOKEN"}'
```

## Core API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/auth/login` | User login | No |
| POST | `/auth/register` | User registration | No |
| POST | `/auth/logout` | User logout | Bearer |
| GET | `/auth/me` | Get current user | Bearer |
| POST | `/auth/password/reset` | Password reset | No |
| POST | `/auth/password/confirm` | Confirm password reset | No |

**Example Login:**
```bash
curl -X POST "https://api.example.com/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

**Response:**
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "type": "user"
    },
    "token": "1|abc123def456...",
    "expires_at": "2025-01-03T12:00:00Z"
  }
}
```

### Profile Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/profile` | Get user profile | Bearer |
| PATCH | `/profile` | Update profile | Bearer |
| PATCH | `/profile/password` | Change password | Bearer |
| PATCH | `/profile/availability` | Set availability status | Bearer |
| DELETE | `/profile/avatar` | Remove avatar | Bearer |
| POST | `/profile/mfa` | Enable MFA | Bearer |
| DELETE | `/profile/mfa` | Disable MFA | Bearer |
| POST | `/profile/mfa/verify` | Verify MFA setup | Bearer |

**Example Profile Update:**
```bash
curl -X PATCH "https://api.example.com/api/v1/profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "availability_status": "busy"
  }'
```

### Account Management

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/accounts` | List user accounts | Bearer | Member |
| POST | `/accounts` | Create account | Bearer | - |
| GET | `/accounts/{id}` | Get account details | Bearer | Member |
| PATCH | `/accounts/{id}` | Update account | Bearer | Admin |
| DELETE | `/accounts/{id}` | Delete account | Bearer | Admin |

### Conversations

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/accounts/{account}/conversations` | List conversations | Bearer | Member |
| POST | `/accounts/{account}/conversations` | Create conversation | Bearer | Member |
| GET | `/accounts/{account}/conversations/{id}` | Get conversation | Bearer | Member |
| PATCH | `/accounts/{account}/conversations/{id}` | Update conversation | Bearer | Member |
| DELETE | `/accounts/{account}/conversations/{id}` | Delete conversation | Bearer | Admin |
| POST | `/accounts/{account}/conversations/{id}/assign` | Assign conversation | Bearer | Member |
| POST | `/accounts/{account}/conversations/{id}/toggle_status` | Toggle status | Bearer | Member |
| GET | `/accounts/{account}/conversations/search` | Search conversations | Bearer | Member |

**Example Conversation Creation:**
```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/conversations" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "contact_id": 123,
    "inbox_id": 456,
    "status": "open"
  }'
```

### Messages

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/accounts/{account}/conversations/{conv}/messages` | List messages | Bearer | Member |
| POST | `/accounts/{account}/conversations/{conv}/messages` | Send message | Bearer | Member |
| GET | `/accounts/{account}/conversations/{conv}/messages/{id}` | Get message | Bearer | Member |
| PATCH | `/accounts/{account}/conversations/{conv}/messages/{id}` | Update message | Bearer | Member |
| DELETE | `/accounts/{account}/conversations/{conv}/messages/{id}` | Delete message | Bearer | Member |

**Example Message Sending:**
```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/conversations/123/messages" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Hello, how can I help you today?",
    "message_type": 1,
    "private": false
  }'
```

### Contacts

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/accounts/{account}/contacts` | List contacts | Bearer | Member |
| POST | `/accounts/{account}/contacts` | Create contact | Bearer | Member |
| GET | `/accounts/{account}/contacts/{id}` | Get contact | Bearer | Member |
| PATCH | `/accounts/{account}/contacts/{id}` | Update contact | Bearer | Member |
| DELETE | `/accounts/{account}/contacts/{id}` | Delete contact | Bearer | Member |
| GET | `/accounts/{account}/contacts/search` | Search contacts | Bearer | Member |

**Example Contact Creation:**
```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/contacts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone_number": "+1234567890",
    "custom_attributes": {
      "company": "Acme Corp",
      "role": "Manager"
    }
  }'
```

## Channel Integration APIs

### WhatsApp Channel

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| POST | `/accounts/{account}/channels/whatsapp` | Create WhatsApp channel | Bearer | Admin |
| PATCH | `/accounts/{account}/channels/whatsapp/{id}` | Update WhatsApp channel | Bearer | Admin |
| POST | `/webhooks/whatsapp` | WhatsApp webhook | No | - |
| GET | `/accounts/{account}/channels/whatsapp/{id}/templates` | Get message templates | Bearer | Admin |

**Example WhatsApp Channel Creation:**
```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/channels/whatsapp" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "WhatsApp Business",
    "phone_number": "+1234567890",
    "provider": "whatsapp_cloud",
    "provider_config": {
      "phone_number_id": "123456789",
      "business_account_id": "987654321",
      "access_token": "your_access_token",
      "verify_token": "your_verify_token"
    }
  }'
```

### Email Channel

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| POST | `/accounts/{account}/channels/email` | Create email channel | Bearer | Admin |
| PATCH | `/accounts/{account}/channels/email/{id}` | Update email channel | Bearer | Admin |
| POST | `/webhooks/email` | Email webhook | No | - |

**Example Email Channel Creation:**
```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/channels/email" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Support Email",
    "email": "support@example.com",
    "imap_enabled": true,
    "imap_address": "imap.gmail.com",
    "imap_port": 993,
    "imap_login": "support@example.com",
    "imap_password": "app_password",
    "imap_enable_ssl": true
  }'
```

### Web Widget Channel

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| POST | `/accounts/{account}/channels/web_widget` | Create web widget | Bearer | Admin |
| PATCH | `/accounts/{account}/channels/web_widget/{id}` | Update web widget | Bearer | Admin |
| GET | `/accounts/{account}/channels/web_widget/{id}/script` | Get widget script | Bearer | Admin |

**Example Web Widget Creation:**
```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/channels/web_widget" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Website Chat",
    "website_name": "My Website",
    "website_url": "https://example.com",
    "widget_color": "#1f93ff",
    "welcome_title": "Welcome!",
    "welcome_tagline": "How can we help you today?"
  }'
```

## Third-Party Integration APIs

### Slack Integration

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/accounts/{account}/integrations/slack` | Get Slack integration | Bearer | Admin |
| POST | `/accounts/{account}/integrations/slack` | Create Slack integration | Bearer | Admin |
| PATCH | `/accounts/{account}/integrations/slack` | Update Slack integration | Bearer | Admin |
| DELETE | `/accounts/{account}/integrations/slack` | Delete Slack integration | Bearer | Admin |
| GET | `/accounts/{account}/integrations/slack/channels` | List Slack channels | Bearer | Admin |

**Example Slack Integration:**
```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/integrations/slack" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "hook_url": "https://hooks.slack.com/services/...",
    "reference_id": "team_id_here"
  }'
```

### Linear Integration

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/accounts/{account}/integrations/linear` | Get Linear integration | Bearer | Admin |
| POST | `/accounts/{account}/integrations/linear` | Create Linear integration | Bearer | Admin |
| GET | `/accounts/{account}/integrations/linear/teams` | List Linear teams | Bearer | Admin |
| POST | `/accounts/{account}/integrations/linear/issues` | Create Linear issue | Bearer | Admin |

## Super Admin APIs

### Dashboard and Analytics

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/super_admin/dashboard` | Get dashboard data | Bearer | Super Admin |
| GET | `/super_admin/analytics` | Get system analytics | Bearer | Super Admin |
| GET | `/super_admin/metrics` | Get system metrics | Bearer | Super Admin |

### Account Management

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/super_admin/accounts` | List all accounts | Bearer | Super Admin |
| POST | `/super_admin/accounts` | Create account | Bearer | Super Admin |
| GET | `/super_admin/accounts/{id}` | Get account details | Bearer | Super Admin |
| PATCH | `/super_admin/accounts/{id}` | Update account | Bearer | Super Admin |
| DELETE | `/super_admin/accounts/{id}` | Delete account | Bearer | Super Admin |

### User Management

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/super_admin/users` | List all users | Bearer | Super Admin |
| POST | `/super_admin/users` | Create user | Bearer | Super Admin |
| GET | `/super_admin/users/{id}` | Get user details | Bearer | Super Admin |
| PATCH | `/super_admin/users/{id}` | Update user | Bearer | Super Admin |
| DELETE | `/super_admin/users/{id}` | Delete user | Bearer | Super Admin |

### System Management

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/super_admin/settings` | Get global settings | Bearer | Super Admin |
| PATCH | `/super_admin/settings` | Update global settings | Bearer | Super Admin |
| POST | `/super_admin/cache/clear` | Clear system cache | Bearer | Super Admin |
| GET | `/super_admin/audit_logs` | Get audit logs | Bearer | Super Admin |

**Example Super Admin Dashboard:**
```bash
curl -X GET "https://api.example.com/api/v1/super_admin/dashboard" \
  -H "Authorization: Bearer SUPER_ADMIN_TOKEN" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "data": {
    "total_accounts": 150,
    "total_users": 1250,
    "total_conversations": 45000,
    "active_conversations": 1200,
    "system_health": {
      "database": "healthy",
      "redis": "healthy",
      "queue": "healthy",
      "websocket": "healthy"
    },
    "recent_activity": [
      {
        "type": "account_created",
        "data": {"account_name": "New Company"},
        "created_at": "2025-01-02T10:30:00Z"
      }
    ]
  }
}
```

## Widget and Public APIs

### Widget Configuration

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/widget/config` | Get widget configuration | No |
| POST | `/widget/contact` | Create/update contact | Widget Token |
| POST | `/widget/conversations` | Create conversation | Widget Token |
| GET | `/widget/conversations/{id}/messages` | Get messages | Widget Token |
| POST | `/widget/conversations/{id}/messages` | Send message | Widget Token |

**Example Widget Configuration:**
```bash
curl -X POST "https://api.example.com/api/v1/widget/config" \
  -H "Content-Type: application/json" \
  -d '{
    "website_token": "your_website_token"
  }'
```

**Response:**
```json
{
  "data": {
    "widget_color": "#1f93ff",
    "welcome_title": "Welcome!",
    "welcome_tagline": "How can we help you today?",
    "pre_chat_form_enabled": false,
    "csat_survey_enabled": true,
    "reply_time": "A few minutes",
    "features": {
      "attachments": true,
      "emoji_picker": true,
      "end_conversation": true
    }
  }
}
```

### Public Inbox API

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/public/inboxes/{identifier}/contacts` | Create contact | No |
| POST | `/public/inboxes/{identifier}/conversations` | Create conversation | No |
| GET | `/public/inboxes/{identifier}/conversations/{id}` | Get conversation | No |

## WebSocket and Real-time APIs

### WebSocket Connection

ClearLine uses Laravel Reverb for WebSocket connections:

```javascript
// Connect to WebSocket
const echo = new Echo({
    broadcaster: 'reverb',
    key: 'your-reverb-key',
    wsHost: 'your-domain.com',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
});

// Listen to conversation updates
echo.private(`conversation.${conversationId}`)
    .listen('MessageCreated', (e) => {
        console.log('New message:', e.message);
    })
    .listen('ConversationUpdated', (e) => {
        console.log('Conversation updated:', e.conversation);
    });

// Listen to user presence
echo.join(`account.${accountId}`)
    .here((users) => {
        console.log('Users online:', users);
    })
    .joining((user) => {
        console.log('User joined:', user);
    })
    .leaving((user) => {
        console.log('User left:', user);
    });
```

### Broadcasting Events

The following events are broadcast in real-time:

| Event | Channel | Description |
|-------|---------|-------------|
| `MessageCreated` | `conversation.{id}` | New message in conversation |
| `ConversationUpdated` | `conversation.{id}` | Conversation status/assignment changed |
| `ConversationCreated` | `account.{id}` | New conversation created |
| `UserPresenceUpdated` | `account.{id}` | User online/offline status changed |
| `NotificationCreated` | `user.{id}` | New notification for user |

## Error Handling

### HTTP Status Codes

| Code | Description | Usage |
|------|-------------|-------|
| 200 | OK | Successful GET, PATCH requests |
| 201 | Created | Successful POST requests |
| 204 | No Content | Successful DELETE requests |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### Error Response Examples

**Validation Error (422):**
```json
{
  "error": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "code": "VALIDATION_ERROR",
  "status": 422
}
```

**Authentication Error (401):**
```json
{
  "error": "Unauthenticated.",
  "code": "UNAUTHENTICATED",
  "status": 401
}
```

**Permission Error (403):**
```json
{
  "error": "This action is unauthorized.",
  "code": "UNAUTHORIZED",
  "status": 403
}
```

**Rate Limit Error (429):**
```json
{
  "error": "Too Many Requests",
  "code": "RATE_LIMIT_EXCEEDED",
  "status": 429,
  "retry_after": 60
}
```

## Rate Limiting

### Default Limits

| Endpoint Type | Limit | Window |
|---------------|-------|--------|
| Authentication | 5 requests | 1 minute |
| API Endpoints | 1000 requests | 1 hour |
| Widget API | 100 requests | 1 minute |
| Webhook Endpoints | 1000 requests | 1 hour |

### Rate Limit Headers

All responses include rate limit headers:

```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1641024000
```

### Handling Rate Limits

```javascript
// Example rate limit handling
async function makeApiRequest(url, options) {
    const response = await fetch(url, options);
    
    if (response.status === 429) {
        const retryAfter = response.headers.get('Retry-After');
        await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
        return makeApiRequest(url, options); // Retry
    }
    
    return response;
}
```

## API Versioning

### Current Version

- **Current Version**: v1
- **Base URL**: `/api/v1/`
- **Deprecation Policy**: 12 months notice for breaking changes

### Version Headers

Include version in Accept header:

```bash
curl -X GET "https://api.example.com/api/v1/accounts" \
  -H "Accept: application/vnd.clearline.v1+json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Backward Compatibility

- Non-breaking changes are deployed to existing versions
- Breaking changes require new version
- Deprecated endpoints return warning headers

## SDK and Client Libraries

### Official SDKs

| Language | Repository | Status |
|----------|------------|--------|
| JavaScript/TypeScript | `clearline-js-sdk` | ✅ Available |
| PHP | `clearline-php-sdk` | ✅ Available |
| Python | `clearline-python-sdk` | 🚧 In Development |
| Ruby | `clearline-ruby-sdk` | 📋 Planned |

### JavaScript SDK Example

```javascript
import ClearLine from '@clearline/sdk';

const client = new ClearLine({
    baseUrl: 'https://api.example.com',
    token: 'your-bearer-token'
});

// Get conversations
const conversations = await client.conversations.list(accountId, {
    status: 'open',
    page: 1,
    per_page: 25
});

// Send message
const message = await client.messages.create(accountId, conversationId, {
    content: 'Hello from SDK!',
    message_type: 1
});

// Listen to real-time events
client.on('message.created', (message) => {
    console.log('New message:', message);
});
```

### PHP SDK Example

```php
<?php
use ClearLine\Client;

$client = new Client([
    'base_url' => 'https://api.example.com',
    'token' => 'your-bearer-token'
]);

// Get conversations
$conversations = $client->conversations()->list($accountId, [
    'status' => 'open',
    'page' => 1,
    'per_page' => 25
]);

// Send message
$message = $client->messages()->create($accountId, $conversationId, [
    'content' => 'Hello from PHP SDK!',
    'message_type' => 1
]);
```

### OpenAPI Code Generation

Generate client libraries using OpenAPI Generator:

```bash
# Install OpenAPI Generator
npm install -g @openapitools/openapi-generator-cli

# Generate JavaScript client
openapi-generator-cli generate \
  -i https://api.example.com/openapi.yaml \
  -g javascript \
  -o ./clearline-js-client

# Generate Python client
openapi-generator-cli generate \
  -i https://api.example.com/openapi.yaml \
  -g python \
  -o ./clearline-python-client

# Generate PHP client
openapi-generator-cli generate \
  -i https://api.example.com/openapi.yaml \
  -g php \
  -o ./clearline-php-client
```

## Testing and Development

### Postman Collection

Import the OpenAPI specification into Postman:

1. Open Postman
2. Click **Import**
3. Select **Link** tab
4. Enter: `https://api.example.com/openapi.yaml`
5. Click **Continue** and **Import**

### Environment Variables for Testing

Set up these environment variables in Postman:

| Variable | Description | Example |
|----------|-------------|---------|
| `baseUrl` | API base URL | `https://api.example.com/api/v1` |
| `bearerToken` | Authentication token | `1\|abc123...` |
| `accountId` | Test account ID | `1` |
| `apiKey` | Platform API key | `platform_key_here` |
| `widgetToken` | Widget auth token | `widget_token_here` |

### API Testing Examples

```bash
# Health check
curl -X GET "https://api.example.com/health"

# Authentication test
curl -X POST "https://api.example.com/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "password"}'

# Authenticated request test
curl -X GET "https://api.example.com/api/v1/profile" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Widget API test
curl -X POST "https://api.example.com/api/v1/widget/config" \
  -H "Content-Type: application/json" \
  -d '{"website_token": "test_token"}'
```

## Support and Resources

### Documentation Links

- [Deployment Guide](./DEPLOYMENT_GUIDE.md)
- [Migration Guide](./RAILS_TO_LARAVEL_MIGRATION_GUIDE.md)
- [Troubleshooting Guide](./TROUBLESHOOTING_GUIDE.md)
- [OpenAPI Specification](./openapi/openapi.bundled.yaml)

### Community and Support

- **GitHub Issues**: Report bugs and feature requests
- **Community Forum**: Get help from other developers
- **Documentation**: Comprehensive guides and tutorials
- **Support Email**: support@clearline.io

### API Status and Updates

- **Status Page**: https://status.clearline.io
- **Changelog**: Track API updates and changes
- **Migration Notices**: Advance notice of breaking changes
- **Maintenance Windows**: Scheduled maintenance notifications

---

**Last Updated:** 2025-01-02  
**API Version:** 1.0  
**Documentation Version:** 1.0
