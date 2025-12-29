# ClearLine Laravel API Documentation

This document provides comprehensive documentation for all API endpoints in the ClearLine Laravel application. Each section describes the purpose, use cases, authentication requirements, and permission setup for the API endpoints.

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Profile Management](#2-profile-management)
3. [Accounts](#3-accounts)
4. [Conversations](#4-conversations)
5. [Messages](#5-messages)
6. [Contacts](#6-contacts)
7. [Inboxes](#7-inboxes)
8. [Teams](#8-teams)
9. [Labels](#9-labels)
10. [Campaigns](#10-campaigns)
11. [Automation Rules](#11-automation-rules)
12. [Canned Responses](#12-canned-responses)
13. [Macros](#13-macros)
14. [Custom Filters](#14-custom-filters)
15. [Custom Attribute Definitions](#15-custom-attribute-definitions)
16. [Agent Bots](#16-agent-bots)
17. [Webhooks](#17-webhooks)
18. [Portals (Help Center)](#18-portals-help-center)
19. [Reports & Analytics](#19-reports--analytics)
20. [SLA Policies](#20-sla-policies)
21. [Notifications](#21-notifications)
22. [Channel Integrations](#22-channel-integrations)
23. [Voice Channel (Twilio)](#23-voice-channel-twilio)
23. [Third-Party Integrations](#23-third-party-integrations)
24. [Widget API (Public)](#24-widget-api-public)
25. [Platform API](#25-platform-api)
26. [Public Inbox API](#26-public-inbox-api)
27. [Super Admin API](#27-super-admin-api)

---

## Authentication Overview

### Types of Authentication

| Type | Description | Use Case |
|------|-------------|----------|
| **Sanctum Token** | Bearer token authentication | Dashboard, mobile apps |
| **API Key** | Platform-level API key | External integrations |
| **Widget Token** | X-Auth-Token header | Chat widget |
| **Public Inbox** | Inbox identifier | Public contact API |

### Permission Levels

| Role | Value | Permissions |
|------|-------|-------------|
| **Agent** | 1 | View/manage conversations, contacts assigned to them |
| **Administrator** | 2 | Full account management, team settings, integrations |
| **Super Admin** | - | Platform-level access, all accounts |

---

## 1. Authentication

### Purpose
Handles user login, registration, and session management.

### Use Cases
- User login/logout
- New user registration
- Session validation

### Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/auth/login` | User login | No |
| POST | `/api/v1/auth/register` | User registration | No |
| POST | `/api/v1/auth/logout` | User logout | Yes |
| GET | `/api/v1/auth/me` | Get current user | Yes |

### Permission Setup
- Login/Register: Public endpoints
- Logout/Me: Requires valid Sanctum token

### Example
```bash
# Login
curl -X POST /api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

---

## 2. Profile Management

### Purpose
Manage authenticated user's profile, settings, and security (MFA).

### Use Cases
- Update user profile information
- Change password
- Set availability status
- Enable/disable MFA
- Manage push notification subscriptions

### Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/profile` | Get profile | Yes |
| PATCH | `/api/v1/profile` | Update profile | Yes |
| PATCH | `/api/v1/profile/password` | Update password | Yes |
| PATCH | `/api/v1/profile/availability` | Set availability | Yes |
| PATCH | `/api/v1/profile/auto_offline` | Set auto-offline | Yes |
| DELETE | `/api/v1/profile/avatar` | Remove avatar | Yes |
| PUT | `/api/v1/profile/set_active_account` | Set active account | Yes |
| POST | `/api/v1/profile/resend_confirmation` | Resend email confirmation | Yes |
| POST | `/api/v1/profile/reset_access_token` | Reset access token | Yes |

### MFA Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/profile/mfa` | Get MFA status | Yes |
| POST | `/api/v1/profile/mfa` | Enable MFA (generate secret) | Yes |
| DELETE | `/api/v1/profile/mfa` | Disable MFA | Yes |
| POST | `/api/v1/profile/mfa/verify` | Verify OTP and activate | Yes |
| POST | `/api/v1/profile/mfa/backup_codes` | Generate new backup codes | Yes |

### Notification Subscriptions

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/notification_subscriptions` | Subscribe to push notifications | Yes |
| DELETE | `/api/v1/notification_subscriptions` | Unsubscribe | Yes |

### Permission Setup
- All endpoints require authenticated user
- Users can only modify their own profile

---

## 3. Accounts

### Purpose
Manage accounts (organizations/workspaces) in the system.

### Use Cases
- Create new accounts/workspaces
- Update account settings
- Manage account features and limits

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts` | List user's accounts | Yes | Member |
| POST | `/api/v1/accounts` | Create account | Yes | Authenticated |
| GET | `/api/v1/accounts/{id}` | Get account details | Yes | Member |
| PATCH | `/api/v1/accounts/{id}` | Update account | Yes | Admin |
| DELETE | `/api/v1/accounts/{id}` | Delete account | Yes | Admin |

### Permission Setup
- **View**: User must be a member of the account
- **Update/Delete**: User must be an administrator (role = 2)
- Enforced via `EnsureAccountAccess` middleware and `AccountPolicy`

---

## 4. Conversations

### Purpose
Manage customer conversations across all channels.

### Use Cases
- View all conversations in an inbox
- Create new conversations
- Assign conversations to agents
- Toggle conversation status (open/resolved/snoozed)
- Add labels and custom attributes

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/conversations` | List conversations | Yes | Member |
| POST | `/api/v1/accounts/{account}/conversations` | Create conversation | Yes | Member |
| GET | `/api/v1/accounts/{account}/conversations/{id}` | Get conversation | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/conversations/{id}` | Update conversation | Yes | Member |
| DELETE | `/api/v1/accounts/{account}/conversations/{id}` | Delete conversation | Yes | Admin |
| GET | `/api/v1/accounts/{account}/conversations/meta` | Get conversation counts | Yes | Member |
| GET | `/api/v1/accounts/{account}/conversations/search` | Search conversations | Yes | Member |
| POST | `/api/v1/accounts/{account}/conversations/filter` | Filter conversations | Yes | Member |
| POST | `/api/v1/accounts/{account}/conversations/{id}/assign` | Assign conversation | Yes | Member |
| POST | `/api/v1/accounts/{account}/conversations/{id}/toggle_status` | Toggle status | Yes | Member |
| POST | `/api/v1/accounts/{account}/conversations/{id}/mute` | Mute conversation | Yes | Member |
| POST | `/api/v1/accounts/{account}/conversations/{id}/unmute` | Unmute conversation | Yes | Member |

### Permission Setup
- **View/Update**: User must be member of account
- **Delete**: User must be administrator (role = 2)
- Enforced via `EnsureAccountAccess` middleware and `ConversationPolicy`

---

## 5. Messages

### Purpose
Manage messages within conversations.

### Use Cases
- Send messages to customers
- View message history
- Add attachments to messages
- Edit/delete messages

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/conversations/{conv}/messages` | List messages | Yes | Member |
| POST | `/api/v1/accounts/{account}/conversations/{conv}/messages` | Create message | Yes | Member |
| GET | `/api/v1/accounts/{account}/conversations/{conv}/messages/{id}` | Get message | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/conversations/{conv}/messages/{id}` | Update message | Yes | Member |
| DELETE | `/api/v1/accounts/{account}/conversations/{conv}/messages/{id}` | Delete message | Yes | Member |

### Permission Setup
- User must be member of the account
- Enforced via `EnsureAccountAccess` middleware and `MessagePolicy`

---

## 6. Contacts

### Purpose
Manage customer contacts and their information.

### Use Cases
- Create and update contact profiles
- Search and filter contacts
- Merge duplicate contacts
- Add notes to contacts
- View contact conversation history

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/contacts` | List contacts | Yes | Member |
| POST | `/api/v1/accounts/{account}/contacts` | Create contact | Yes | Member |
| GET | `/api/v1/accounts/{account}/contacts/{id}` | Get contact | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/contacts/{id}` | Update contact | Yes | Member |
| DELETE | `/api/v1/accounts/{account}/contacts/{id}` | Delete contact | Yes | Member |
| GET | `/api/v1/accounts/{account}/contacts/search` | Search contacts | Yes | Member |
| GET | `/api/v1/accounts/{account}/contacts/filter` | Filter contacts | Yes | Member |

### Contact Notes

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/contacts/{contact}/notes` | List notes | Yes | Member |
| POST | `/api/v1/accounts/{account}/contacts/{contact}/notes` | Create note | Yes | Member |
| DELETE | `/api/v1/accounts/{account}/contacts/{contact}/notes/{id}` | Delete note | Yes | Member |

### Permission Setup
- User must be member of the account
- Enforced via `EnsureAccountAccess` middleware and `ContactPolicy`

---

## 7. Inboxes

### Purpose
Manage communication channels (inboxes) for the account.

### Use Cases
- Create different channel types (email, web, WhatsApp, etc.)
- Configure inbox settings
- Manage inbox team members
- Set working hours

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/inboxes` | List inboxes | Yes | Member |
| POST | `/api/v1/accounts/{account}/inboxes` | Create inbox | Yes | Admin |
| GET | `/api/v1/accounts/{account}/inboxes/{id}` | Get inbox | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/inboxes/{id}` | Update inbox | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/inboxes/{id}` | Delete inbox | Yes | Admin |

### Permission Setup
- **View**: User must be member of the account
- **Create/Update/Delete**: User must be administrator (role = 2)
- Enforced via `EnsureAccountAccess` middleware and `InboxPolicy`

---

## 8. Teams

### Purpose
Organize agents into teams for better conversation routing.

### Use Cases
- Create and manage teams
- Assign agents to teams
- Route conversations to teams

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/teams` | List teams | Yes | Member |
| POST | `/api/v1/accounts/{account}/teams` | Create team | Yes | Admin |
| GET | `/api/v1/accounts/{account}/teams/{id}` | Get team | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/teams/{id}` | Update team | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/teams/{id}` | Delete team | Yes | Admin |
| GET | `/api/v1/accounts/{account}/teams/{id}/members` | List members | Yes | Member |
| POST | `/api/v1/accounts/{account}/teams/{id}/members` | Add members | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/teams/{id}/members` | Remove members | Yes | Admin |

### Permission Setup
- **View**: User must be member of the account
- **Create/Update/Delete**: User must be administrator (role = 2)

---

## 9. Labels

### Purpose
Categorize and organize conversations with labels.

### Use Cases
- Create color-coded labels
- Apply labels to conversations
- Filter conversations by labels

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/labels` | List labels | Yes | Member |
| POST | `/api/v1/accounts/{account}/labels` | Create label | Yes | Admin |
| GET | `/api/v1/accounts/{account}/labels/{id}` | Get label | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/labels/{id}` | Update label | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/labels/{id}` | Delete label | Yes | Admin |

### Permission Setup
- **View**: User must be member of the account
- **Create/Update/Delete**: User must be administrator (role = 2)

---

## 10. Campaigns

### Purpose
Create and manage marketing campaigns for proactive customer engagement.

### Use Cases
- Create one-time or ongoing campaigns
- Target specific audiences with segment filters
- Send promotional messages
- Track campaign performance

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/campaigns` | List campaigns | Yes | Member |
| POST | `/api/v1/accounts/{account}/campaigns` | Create campaign | Yes | Admin |
| GET | `/api/v1/accounts/{account}/campaigns/{id}` | Get campaign | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/campaigns/{id}` | Update campaign | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/campaigns/{id}` | Delete campaign | Yes | Admin |

### Permission Setup
- **View**: User must be member of the account
- **Create/Update/Delete**: User must be administrator (role = 2)

---

## 11. Automation Rules

### Purpose
Automate actions based on conversation events and conditions.

### Use Cases
- Auto-assign conversations
- Send automatic replies
- Apply labels based on keywords
- Trigger webhooks on events

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/automation_rules` | List rules | Yes | Member |
| POST | `/api/v1/accounts/{account}/automation_rules` | Create rule | Yes | Admin |
| GET | `/api/v1/accounts/{account}/automation_rules/{id}` | Get rule | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/automation_rules/{id}` | Update rule | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/automation_rules/{id}` | Delete rule | Yes | Admin |
| POST | `/api/v1/accounts/{account}/automation_rules/{id}/clone` | Clone rule | Yes | Admin |

### Permission Setup
- **View**: User must be member of the account
- **Create/Update/Delete**: User must be administrator (role = 2)

---

## 12. Canned Responses

### Purpose
Pre-defined message templates for quick responses.

### Use Cases
- Create reusable response templates
- Speed up agent response time
- Ensure consistent messaging

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/canned_responses` | List responses | Yes | Member |
| POST | `/api/v1/accounts/{account}/canned_responses` | Create response | Yes | Member |
| GET | `/api/v1/accounts/{account}/canned_responses/{id}` | Get response | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/canned_responses/{id}` | Update response | Yes | Member |
| DELETE | `/api/v1/accounts/{account}/canned_responses/{id}` | Delete response | Yes | Member |

### Permission Setup
- All members can create and manage canned responses

---

## 13. Macros

### Purpose
Automate multiple actions with a single click.

### Use Cases
- Execute predefined action sequences
- Update multiple conversation attributes
- Send templated messages with actions

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/macros` | List macros | Yes | Member |
| POST | `/api/v1/accounts/{account}/macros` | Create macro | Yes | Member |
| GET | `/api/v1/accounts/{account}/macros/{id}` | Get macro | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/macros/{id}` | Update macro | Yes | Member |
| DELETE | `/api/v1/accounts/{account}/macros/{id}` | Delete macro | Yes | Member |
| POST | `/api/v1/accounts/{account}/macros/{id}/execute` | Execute macro | Yes | Member |

### Permission Setup
- All members can create personal macros
- Visibility can be personal or global

---

## 14. Custom Filters

### Purpose
Save complex filter queries for reuse.

### Use Cases
- Save frequently used conversation filters
- Quick access to filtered views
- Share filters across team

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/custom_filters` | List filters | Yes | Member |
| POST | `/api/v1/accounts/{account}/custom_filters` | Create filter | Yes | Member |
| GET | `/api/v1/accounts/{account}/custom_filters/{id}` | Get filter | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/custom_filters/{id}` | Update filter | Yes | Member |
| DELETE | `/api/v1/accounts/{account}/custom_filters/{id}` | Delete filter | Yes | Member |

### Permission Setup
- All members can create and manage their custom filters

---

## 15. Custom Attribute Definitions

### Purpose
Define custom fields for contacts and conversations.

### Use Cases
- Add custom data fields
- Capture business-specific information
- Extend contact/conversation metadata

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/custom_attribute_definitions` | List definitions | Yes | Member |
| POST | `/api/v1/accounts/{account}/custom_attribute_definitions` | Create definition | Yes | Admin |
| GET | `/api/v1/accounts/{account}/custom_attribute_definitions/{id}` | Get definition | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/custom_attribute_definitions/{id}` | Update definition | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/custom_attribute_definitions/{id}` | Delete definition | Yes | Admin |

### Permission Setup
- **View**: User must be member of the account
- **Create/Update/Delete**: User must be administrator (role = 2)

---

## 16. Agent Bots

### Purpose
Configure AI/webhook-based bots for automated responses.

### Use Cases
- Set up chatbots for initial responses
- Integrate with Dialogflow/custom AI
- Handoff to human agents

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/agent_bots` | List bots | Yes | Member |
| POST | `/api/v1/accounts/{account}/agent_bots` | Create bot | Yes | Admin |
| GET | `/api/v1/accounts/{account}/agent_bots/{id}` | Get bot | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/agent_bots/{id}` | Update bot | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/agent_bots/{id}` | Delete bot | Yes | Admin |

### Permission Setup
- **View**: User must be member of the account
- **Create/Update/Delete**: User must be administrator (role = 2)

---

## 17. Webhooks

### Purpose
Send real-time notifications to external services.

### Use Cases
- Sync data with CRM systems
- Trigger external workflows
- Log events to analytics

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/webhooks` | List webhooks | Yes | Admin |
| POST | `/api/v1/accounts/{account}/webhooks` | Create webhook | Yes | Admin |
| GET | `/api/v1/accounts/{account}/webhooks/{id}` | Get webhook | Yes | Admin |
| PATCH | `/api/v1/accounts/{account}/webhooks/{id}` | Update webhook | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/webhooks/{id}` | Delete webhook | Yes | Admin |

### Permission Setup
- **All operations**: User must be administrator (role = 2)

---

## 18. Portals (Help Center)

### Purpose
Manage self-service help center with articles and categories.

### Use Cases
- Create knowledge base articles
- Organize articles in categories
- Embed help center on website

### Portal Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/portals` | List portals | Yes | Member |
| POST | `/api/v1/accounts/{account}/portals` | Create portal | Yes | Admin |
| GET | `/api/v1/accounts/{account}/portals/{id}` | Get portal | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/portals/{id}` | Update portal | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/portals/{id}` | Delete portal | Yes | Admin |

### Article Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/articles` | List articles | Yes | Member |
| POST | `/api/v1/accounts/{account}/articles` | Create article | Yes | Member |
| GET | `/api/v1/accounts/{account}/articles/{id}` | Get article | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/articles/{id}` | Update article | Yes | Member |
| DELETE | `/api/v1/accounts/{account}/articles/{id}` | Delete article | Yes | Admin |

### Category Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/categories` | List categories | Yes | Member |
| POST | `/api/v1/accounts/{account}/categories` | Create category | Yes | Admin |
| GET | `/api/v1/accounts/{account}/categories/{id}` | Get category | Yes | Member |
| PATCH | `/api/v1/accounts/{account}/categories/{id}` | Update category | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/categories/{id}` | Delete category | Yes | Admin |

### Permission Setup
- **View**: User must be member of the account
- **Create Portal/Category/Delete Article**: User must be administrator (role = 2)
- **Create/Update Articles**: All members can contribute

---

## 19. Reports & Analytics

### Purpose
Generate reports on team performance and conversations.

### Use Cases
- Track conversation metrics
- Monitor agent performance
- Analyze response times
- Export data for analysis

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/reports` | Get report summary | Yes | Admin |
| GET | `/api/v1/accounts/{account}/reports/conversations` | Conversation reports | Yes | Admin |
| GET | `/api/v1/accounts/{account}/reports/agents` | Agent reports | Yes | Admin |
| GET | `/api/v1/accounts/{account}/reports/teams` | Team reports | Yes | Admin |
| GET | `/api/v1/accounts/{account}/reports/inboxes` | Inbox reports | Yes | Admin |
| GET | `/api/v1/accounts/{account}/reports/labels` | Label reports | Yes | Admin |
| GET | `/api/v1/accounts/{account}/reports/download` | Export reports | Yes | Admin |

### Permission Setup
- **All operations**: User must be administrator (role = 2)

---

## 20. SLA Policies

### Purpose
Define service level agreements for response times.

### Use Cases
- Set response time targets
- Monitor SLA breaches
- Prioritize conversations based on SLA

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/sla_policies` | List SLA policies | Yes | Admin |
| POST | `/api/v1/accounts/{account}/sla_policies` | Create SLA policy | Yes | Admin |
| GET | `/api/v1/accounts/{account}/sla_policies/{id}` | Get SLA policy | Yes | Admin |
| PATCH | `/api/v1/accounts/{account}/sla_policies/{id}` | Update SLA policy | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/sla_policies/{id}` | Delete SLA policy | Yes | Admin |
| GET | `/api/v1/accounts/{account}/sla_policies/{id}/breaches` | Get SLA breaches | Yes | Admin |
| GET | `/api/v1/accounts/{account}/sla_policies/{id}/metrics` | Get SLA metrics | Yes | Admin |

### Permission Setup
- **All operations**: User must be administrator (role = 2)

---

## 21. Notifications

### Purpose
Manage in-app notifications for users.

### Use Cases
- View unread notifications
- Mark notifications as read
- Subscribe to push notifications

### Endpoints

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/notifications` | List notifications | Yes | User |
| GET | `/api/v1/notifications/unread_count` | Get unread count | Yes | User |
| POST | `/api/v1/notifications/{id}/read` | Mark as read | Yes | User |
| POST | `/api/v1/notifications/read_all` | Mark all as read | Yes | User |
| DELETE | `/api/v1/notifications/{id}` | Delete notification | Yes | User |

### Permission Setup
- Users can only access their own notifications

---

## 22. Channel Integrations
## 23. Voice Channel (Twilio)

### Purpose
Integrate Twilio voice calls and conferences with ClearLine. Handles incoming calls, call status, and conference events.

### Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/webhooks/voice/call/{phone}` | Twilio webhook for incoming calls (returns TwiML) |
| POST | `/api/v1/webhooks/voice/status/{phone}` | Twilio webhook for call status events |
| POST | `/api/v1/webhooks/voice/conference_status/{phone}` | Twilio webhook for conference status events |

### Example Payloads

#### Incoming Call (TwiML)
```
POST /api/v1/webhooks/voice/call/+1234567890
Content-Type: application/x-www-form-urlencoded

From=+15551234567&To=+1234567890&CallSid=CA123...&Direction=inbound
```
Response:
```xml
<Response>
  <Dial>
    <Conference startConferenceOnEnter="false" endConferenceOnExit="false" ...>conf_123</Conference>
  </Dial>
</Response>
```

#### Call Status Event
```
POST /api/v1/webhooks/voice/status/+1234567890
Content-Type: application/x-www-form-urlencoded

CallSid=CA123...&CallStatus=completed
```

#### Conference Status Event
```
POST /api/v1/webhooks/voice/conference_status/+1234567890
Content-Type: application/x-www-form-urlencoded

ConferenceSid=CF123...&StatusCallbackEvent=participant-join&ParticipantLabel=agent
```

### Setup
Configure your Twilio number to use these endpoints for voice and status callbacks. See the main README for more details.

### Purpose
Connect various messaging channels to handle customer conversations.

### Available Channels

| Channel | Description | Controller |
|---------|-------------|------------|
| **Web Widget** | Embeddable chat widget | `WebWidgetController` |
| **Email** | Email inbox with IMAP/SMTP | `EmailController` |
| **WhatsApp** | WhatsApp Business API | `WhatsAppController` |
| **Facebook** | Facebook Messenger | `FacebookController` |
| **Twitter** | Twitter Direct Messages | `TwitterController` |
| **Telegram** | Telegram Bot | `TelegramController` |
| **SMS** | Twilio/Bandwidth SMS | `SmsController` |
| **Line** | LINE Messaging API | `LineController` |
| **API** | Custom API channel | `ApiController` |

### Endpoints Pattern (per channel)

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| POST | `/api/v1/accounts/{account}/channels/{type}` | Create channel | Yes | Admin |
| PATCH | `/api/v1/accounts/{account}/channels/{type}/{id}` | Update channel | Yes | Admin |
| POST | `/api/v1/webhooks/{type}` | Receive webhook | No | - |

### Permission Setup
- **Create/Update**: User must be administrator (role = 2)
- **Webhooks**: Verified by platform-specific signatures

---

## 23. Third-Party Integrations

### Purpose
Connect with external services for enhanced functionality.

### Available Integrations

| Integration | Description | Use Cases |
|-------------|-------------|-----------|
| **Slack** | Team collaboration | Notifications, conversation sync |
| **Dialogflow** | AI chatbot | Automated responses |
| **OpenAI** | AI assistance | Reply suggestions, summarization |
| **Linear** | Issue tracking | Create issues from conversations |
| **Shopify** | E-commerce | Customer order lookup |

### Endpoints Pattern (per integration)

| Method | Endpoint | Description | Auth Required | Permission |
|--------|----------|-------------|---------------|------------|
| GET | `/api/v1/accounts/{account}/integrations/{type}` | Get integration | Yes | Admin |
| POST | `/api/v1/accounts/{account}/integrations/{type}` | Create integration | Yes | Admin |
| PATCH | `/api/v1/accounts/{account}/integrations/{type}` | Update integration | Yes | Admin |
| DELETE | `/api/v1/accounts/{account}/integrations/{type}` | Delete integration | Yes | Admin |

### Permission Setup
- **All operations**: User must be administrator (role = 2)

---

## 24. Widget API (Public)

### Purpose
Public API endpoints for the embeddable chat widget. No Sanctum authentication required - uses widget token.

### Authentication
Uses `X-Auth-Token` header with the contact inbox token (source_id).

### Use Cases
- Initialize chat widget on customer website
- Send/receive messages from widget
- Track visitor events

### Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/widget/config` | Get widget config | Website Token |
| GET | `/api/v1/widget/campaigns` | Get active campaigns | Website Token |
| GET | `/api/v1/widget/contact` | Get current contact | Widget Token |
| PATCH | `/api/v1/widget/contact` | Update contact | Widget Token |
| PATCH | `/api/v1/widget/contact/set_user` | Set user identifier | Widget Token |
| GET | `/api/v1/widget/conversations` | List conversations | Widget Token |
| POST | `/api/v1/widget/conversations` | Create conversation | Website Token |
| GET | `/api/v1/widget/messages` | List messages | Widget Token |
| POST | `/api/v1/widget/messages` | Send message | Widget Token |
| GET | `/api/v1/widget/inbox_members` | Get available agents | Widget Token |
| POST | `/api/v1/widget/labels` | Add label | Widget Token |
| POST | `/api/v1/widget/events` | Track event | Widget Token |
| POST | `/api/v1/widget/direct_uploads` | Upload file | Widget Token |

### Permission Setup
- Public endpoints, authenticated via token in header
- Contact can only access their own conversations

---

## 25. Platform API

### Purpose
Platform-level API for managing users and accounts across the system. Used for SSO integrations and platform management.

### Authentication
Uses platform API key (configured at instance level).

### Use Cases
- SSO user provisioning
- Multi-tenant account management
- Platform-level bot management

### Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/platform/users/{id}` | Get user | Platform Key |
| POST | `/api/v1/platform/users` | Create user | Platform Key |
| PATCH | `/api/v1/platform/users/{id}` | Update user | Platform Key |
| DELETE | `/api/v1/platform/users/{id}` | Delete user | Platform Key |
| GET | `/api/v1/platform/users/{id}/login` | SSO login URL | Platform Key |
| POST | `/api/v1/platform/users/{id}/token` | Generate token | Platform Key |
| GET | `/api/v1/platform/accounts` | List accounts | Platform Key |
| POST | `/api/v1/platform/accounts` | Create account | Platform Key |
| GET | `/api/v1/platform/accounts/{id}` | Get account | Platform Key |
| PATCH | `/api/v1/platform/accounts/{id}` | Update account | Platform Key |
| DELETE | `/api/v1/platform/accounts/{id}` | Delete account | Platform Key |
| GET | `/api/v1/platform/accounts/{id}/account_users` | List account users | Platform Key |
| POST | `/api/v1/platform/accounts/{id}/account_users` | Add user to account | Platform Key |
| DELETE | `/api/v1/platform/accounts/{id}/account_users` | Remove user | Platform Key |
| GET | `/api/v1/platform/agent_bots` | List global bots | Platform Key |
| POST | `/api/v1/platform/agent_bots` | Create global bot | Platform Key |

### Permission Setup
- All endpoints require valid platform API key
- Full platform-level access

---

## 26. Public Inbox API

### Purpose
Public API for contacts to interact with inboxes directly, without going through the widget.

### Authentication
Uses inbox identifier. No user authentication required.

### Use Cases
- Mobile app integrations
- Custom chat interfaces
- Third-party chat clients

### Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/public/inboxes/{inbox}/contacts` | Create contact | None |
| GET | `/api/v1/public/inboxes/{inbox}/contacts/{id}` | Get contact | None |
| PATCH | `/api/v1/public/inboxes/{inbox}/contacts/{id}` | Update contact | None |
| GET | `/api/v1/public/inboxes/{inbox}/contacts/{id}/conversations` | List conversations | None |
| POST | `/api/v1/public/inboxes/{inbox}/contacts/{id}/conversations` | Create conversation | None |
| GET | `/api/v1/public/inboxes/{inbox}/contacts/{id}/conversations/{conv}` | Get conversation | None |
| POST | `/api/v1/public/inboxes/{inbox}/contacts/{id}/conversations/{conv}/toggle_status` | Toggle status | None |
| GET | `/api/v1/public/inboxes/{inbox}/contacts/{id}/conversations/{conv}/messages` | List messages | None |
| POST | `/api/v1/public/inboxes/{inbox}/contacts/{id}/conversations/{conv}/messages` | Send message | None |

### CSAT Survey

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/public/csat/{uuid}` | Get CSAT survey | None |
| POST | `/api/v1/public/csat/{uuid}` | Submit CSAT response | None |

### Permission Setup
- Public endpoints, no authentication required
- Access scoped to specific inbox

---

## 27. Super Admin API

### Purpose
Administrative API for platform-wide management. Only accessible by super administrators.

### Authentication
Requires Sanctum token + super_admin role.

### Use Cases
- Manage all accounts in the system
- Monitor system health
- Configure platform settings
- Manage global agent bots

### Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/super_admin/accounts` | List all accounts | Super Admin |
| POST | `/api/v1/super_admin/accounts` | Create account | Super Admin |
| GET | `/api/v1/super_admin/accounts/{id}` | Get account | Super Admin |
| PATCH | `/api/v1/super_admin/accounts/{id}` | Update account | Super Admin |
| DELETE | `/api/v1/super_admin/accounts/{id}` | Delete account | Super Admin |
| POST | `/api/v1/super_admin/accounts/{id}/seed` | Seed demo data | Super Admin |
| GET | `/api/v1/super_admin/users` | List all users | Super Admin |
| POST | `/api/v1/super_admin/users` | Create user | Super Admin |
| GET | `/api/v1/super_admin/users/{id}` | Get user | Super Admin |
| PATCH | `/api/v1/super_admin/users/{id}` | Update user | Super Admin |
| DELETE | `/api/v1/super_admin/users/{id}` | Delete user | Super Admin |
| GET | `/api/v1/super_admin/agent_bots` | List global bots | Super Admin |
| POST | `/api/v1/super_admin/agent_bots` | Create global bot | Super Admin |
| GET | `/api/v1/super_admin/platform_apps` | List platform apps | Super Admin |
| POST | `/api/v1/super_admin/platform_apps` | Create platform app | Super Admin |
| GET | `/api/v1/super_admin/instance_status` | System status | Super Admin |
| GET | `/api/v1/super_admin/installation_configs` | Get configs | Super Admin |
| POST | `/api/v1/super_admin/installation_configs` | Update configs | Super Admin |
| GET | `/api/v1/super_admin/access_tokens` | List tokens | Super Admin |
| POST | `/api/v1/super_admin/access_tokens` | Create token | Super Admin |
| DELETE | `/api/v1/super_admin/access_tokens/{id}` | Revoke token | Super Admin |

### Permission Setup
- All endpoints require `super_admin` role
- Enforced via `EnsureSuperAdmin` middleware

---

## Permission Summary Matrix

| API Section | Agent | Admin | Super Admin |
|-------------|-------|-------|-------------|
| Authentication | ✅ | ✅ | ✅ |
| Profile | Own | Own | Own |
| Accounts | View | Full | Full |
| Conversations | View/Update | Full | Full |
| Messages | Full | Full | Full |
| Contacts | Full | Full | Full |
| Inboxes | View | Full | Full |
| Teams | View | Full | Full |
| Labels | View | Full | Full |
| Campaigns | View | Full | Full |
| Automation Rules | View | Full | Full |
| Canned Responses | Full | Full | Full |
| Macros | Full | Full | Full |
| Custom Filters | Full | Full | Full |
| Custom Attributes | View | Full | Full |
| Agent Bots | View | Full | Full |
| Webhooks | ❌ | Full | Full |
| Portals | View | Full | Full |
| Reports | ❌ | Full | Full |
| SLA Policies | ❌ | Full | Full |
| Notifications | Own | Own | Own |
| Super Admin | ❌ | ❌ | Full |

---

## Error Responses

All API endpoints return consistent error responses:

```json
{
  "error": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 204 | No Content (Delete) |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Rate Limited |
| 500 | Server Error |

---

## Rate Limiting

API requests are rate limited based on authentication:

| Auth Type | Limit |
|-----------|-------|
| Unauthenticated | 60/minute |
| Authenticated | 500/minute |
| Widget | 100/minute |

---

**Last Updated:** 2025-12-27
**Version:** 7.0.0
