# ClearLine OpenAPI Specification

This directory contains OpenAPI 3.0 specifications for all ClearLine API endpoints.

## Files

| File | Description |
|------|-------------|
| `openapi.yaml` | Main specification with schemas and security definitions |
| `paths/` | Directory containing path definitions by section |

## Path Files

| File | Endpoints |
|------|-----------|
| `authentication.yaml` | Login, register, logout, current user |
| `profile.yaml` | Profile management, MFA setup |
| `conversations.yaml` | Conversation CRUD, search, filter, status |
| `messages.yaml` | Message CRUD within conversations |
| `contacts.yaml` | Contact CRUD, search, import/export, notes |
| `inboxes.yaml` | Inbox management, members, agent bots |
| `teams_labels_webhooks.yaml` | Teams, labels, and webhooks |
| `widget.yaml` | Public widget API endpoints |
| `platform.yaml` | Platform-level API for SSO and management |
| `integrations.yaml` | Slack, Dialogflow, OpenAI, Linear, Shopify |

## Usage

### Viewing Documentation

You can use any OpenAPI-compatible tool to view this documentation:

1. **Swagger UI**: https://editor.swagger.io/
2. **Redoc**: https://redocly.github.io/redoc/
3. **Stoplight**: https://stoplight.io/
4. **Postman**: Import the openapi.yaml file

### Testing with Postman

1. Open Postman
2. Click **Import** > **File** > select `openapi.yaml`
3. The collection will be created with all endpoints
4. Set up environment variables:
   - `baseUrl`: Your API base URL (e.g., `http://localhost:8000/api/v1`)
   - `bearerToken`: Your authentication token
   - `accountId`: Your account ID

### Generating Client SDKs

Use OpenAPI Generator to create client libraries:

```bash
# Install OpenAPI Generator
npm install -g @openapitools/openapi-generator-cli

# Generate JavaScript client
openapi-generator-cli generate -i openapi.yaml -g javascript -o ./sdk/javascript

# Generate Python client
openapi-generator-cli generate -i openapi.yaml -g python -o ./sdk/python

# Generate PHP client
openapi-generator-cli generate -i openapi.yaml -g php -o ./sdk/php
```

## Authentication

The API uses three authentication methods:

### 1. Bearer Token (Sanctum)

Most authenticated endpoints use Bearer token authentication:

```bash
curl -X GET "https://api.example.com/api/v1/profile" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 2. Platform API Key

Platform-level endpoints use API key authentication:

```bash
curl -X GET "https://api.example.com/api/v1/platform/users/1" \
  -H "api_access_token: YOUR_PLATFORM_API_KEY"
```

### 3. Widget Token

Widget endpoints use X-Auth-Token header:

```bash
curl -X GET "https://api.example.com/api/v1/widget/contact" \
  -H "X-Auth-Token: WIDGET_AUTH_TOKEN"
```

## Example Requests

### Login

```bash
curl -X POST "https://api.example.com/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

### List Conversations

```bash
curl -X GET "https://api.example.com/api/v1/accounts/1/conversations?status=open" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Send Message

```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/conversations/1/messages" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Hello, how can I help you?",
    "message_type": "outgoing"
  }'
```

### Create Contact

```bash
curl -X POST "https://api.example.com/api/v1/accounts/1/contacts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone_number": "+1234567890"
  }'
```

### Widget: Get Config

```bash
curl -X POST "https://api.example.com/api/v1/widget/config" \
  -H "Content-Type: application/json" \
  -d '{
    "website_token": "YOUR_WEBSITE_TOKEN"
  }'
```

## Endpoints Summary

| Category | Endpoints | Auth Required |
|----------|-----------|---------------|
| Authentication | 4 | Partial |
| Profile | 12 | Yes |
| Accounts | 5 | Yes |
| Conversations | 20+ | Yes |
| Messages | 5 | Yes |
| Contacts | 15+ | Yes |
| Inboxes | 15+ | Yes (Admin for write) |
| Teams | 8 | Yes (Admin for write) |
| Labels | 5 | Yes (Admin for write) |
| Webhooks | 5 | Yes (Admin) |
| Campaigns | 5 | Yes (Admin for write) |
| Automation Rules | 6 | Yes (Admin for write) |
| Canned Responses | 5 | Yes |
| Macros | 6 | Yes |
| Custom Filters | 5 | Yes |
| Custom Attributes | 5 | Yes (Admin for write) |
| Agent Bots | 5 | Yes (Admin for write) |
| Portals | 10+ | Yes |
| Reports | 7 | Yes (Admin) |
| SLA Policies | 7 | Yes (Admin) |
| Notifications | 6 | Yes |
| Widget | 15 | Widget Token |
| Platform | 18 | Platform Key |
| Super Admin | 25+ | Super Admin |
| Integrations | 30+ | Yes (Admin) |
| Channels | 20+ | Yes (Admin) |

**Total: 250+ endpoints**

## Error Responses

All endpoints return consistent error responses:

```json
{
  "error": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 204 | No Content (successful delete) |
| 400 | Bad Request |
| 401 | Unauthenticated |
| 403 | Forbidden (insufficient permissions) |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Rate Limited |
| 500 | Server Error |

---

**Last Updated:** 2025-12-27
**OpenAPI Version:** 3.0.3
