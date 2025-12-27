# ClearLine API Migration Comparison

This document compares the Chatwoot Rails API functionality with the ClearLine Laravel implementation to ensure complete migration coverage.

## Migration Status: ✅ COMPLETE

All Chatwoot Rails API endpoints have been successfully migrated to ClearLine Laravel 12.

**Last Updated:** 2025-12-27

---

## API Endpoint Comparison

### Authentication & Authorization

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/auth/sign_in | POST /api/v1/auth/login | ✅ |
| POST /api/v1/auth/sign_up | POST /api/v1/auth/register | ✅ |
| DELETE /api/v1/auth/sign_out | POST /api/v1/auth/logout | ✅ |
| GET /api/v1/auth/validate_token | GET /api/v1/auth/me | ✅ |

### Profile Management

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/profile | GET /api/v1/profile | ✅ |
| PATCH /api/v1/profile | PATCH /api/v1/profile | ✅ |
| PATCH /api/v1/profile/password | PATCH /api/v1/profile/password | ✅ |
| PATCH /api/v1/profile/availability | PATCH /api/v1/profile/availability | ✅ |
| PATCH /api/v1/profile/auto_offline | PATCH /api/v1/profile/auto_offline | ✅ |
| DELETE /api/v1/profile/avatar | DELETE /api/v1/profile/avatar | ✅ |
| PUT /api/v1/profile/set_active_account | PUT /api/v1/profile/set_active_account | ✅ |
| POST /api/v1/profile/resend_confirmation | POST /api/v1/profile/resend_confirmation | ✅ |
| POST /api/v1/profile/reset_access_token | POST /api/v1/profile/reset_access_token | ✅ |

### MFA (Multi-Factor Authentication)

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/profile/mfa | GET /api/v1/profile/mfa | ✅ |
| POST /api/v1/profile/mfa | POST /api/v1/profile/mfa | ✅ |
| DELETE /api/v1/profile/mfa | DELETE /api/v1/profile/mfa | ✅ |
| POST /api/v1/profile/mfa/verify | POST /api/v1/profile/mfa/verify | ✅ |
| POST /api/v1/profile/mfa/backup_codes | POST /api/v1/profile/mfa/backup_codes | ✅ |

### Notification Subscriptions

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/notification_subscriptions | POST /api/v1/notification_subscriptions | ✅ |
| DELETE /api/v1/notification_subscriptions | DELETE /api/v1/notification_subscriptions | ✅ |

### Accounts

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts | GET /api/v1/accounts | ✅ |
| POST /api/v1/accounts | POST /api/v1/accounts | ✅ |
| GET /api/v1/accounts/:id | GET /api/v1/accounts/{account} | ✅ |
| PATCH /api/v1/accounts/:id | PATCH /api/v1/accounts/{account} | ✅ |
| DELETE /api/v1/accounts/:id | DELETE /api/v1/accounts/{account} | ✅ |

### Conversations

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/conversations | GET /api/v1/accounts/{account}/conversations | ✅ |
| POST /api/v1/accounts/:id/conversations | POST /api/v1/accounts/{account}/conversations | ✅ |
| GET /api/v1/accounts/:id/conversations/:id | GET /api/v1/accounts/{account}/conversations/{conversation} | ✅ |
| PATCH /api/v1/accounts/:id/conversations/:id | PATCH /api/v1/accounts/{account}/conversations/{conversation} | ✅ |
| DELETE /api/v1/accounts/:id/conversations/:id | DELETE /api/v1/accounts/{account}/conversations/{conversation} | ✅ |
| POST /api/v1/accounts/:id/conversations/:id/toggle_status | POST /api/v1/accounts/{account}/conversations/{conversation}/resolve | ✅ |
| POST /api/v1/accounts/:id/conversations/:id/assignments | POST /api/v1/accounts/{account}/conversations/{conversation}/assign | ✅ |

### Messages

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/conversations/:id/messages | GET /api/v1/accounts/{account}/conversations/{conversation}/messages | ✅ |
| POST /api/v1/accounts/:id/conversations/:id/messages | POST /api/v1/accounts/{account}/conversations/{conversation}/messages | ✅ |
| DELETE /api/v1/accounts/:id/conversations/:id/messages/:id | DELETE /api/v1/accounts/{account}/conversations/{conversation}/messages/{message} | ✅ |

### Contacts

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/contacts | GET /api/v1/accounts/{account}/contacts | ✅ |
| POST /api/v1/accounts/:id/contacts | POST /api/v1/accounts/{account}/contacts | ✅ |
| GET /api/v1/accounts/:id/contacts/:id | GET /api/v1/accounts/{account}/contacts/{contact} | ✅ |
| PATCH /api/v1/accounts/:id/contacts/:id | PATCH /api/v1/accounts/{account}/contacts/{contact} | ✅ |
| DELETE /api/v1/accounts/:id/contacts/:id | DELETE /api/v1/accounts/{account}/contacts/{contact} | ✅ |
| POST /api/v1/accounts/:id/contacts/:id/merge | POST /api/v1/accounts/{account}/contacts/{contact}/merge | ✅ |

### Contact Notes

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/contacts/:id/notes | GET /api/v1/accounts/{account}/contacts/{contact}/notes | ✅ |
| POST /api/v1/accounts/:id/contacts/:id/notes | POST /api/v1/accounts/{account}/contacts/{contact}/notes | ✅ |
| DELETE /api/v1/accounts/:id/contacts/:id/notes/:id | DELETE /api/v1/accounts/{account}/contacts/{contact}/notes/{note} | ✅ |

### Inboxes

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/inboxes | GET /api/v1/accounts/{account}/inboxes | ✅ |
| POST /api/v1/accounts/:id/inboxes | POST /api/v1/accounts/{account}/inboxes | ✅ |
| GET /api/v1/accounts/:id/inboxes/:id | GET /api/v1/accounts/{account}/inboxes/{inbox} | ✅ |
| PATCH /api/v1/accounts/:id/inboxes/:id | PATCH /api/v1/accounts/{account}/inboxes/{inbox} | ✅ |
| DELETE /api/v1/accounts/:id/inboxes/:id | DELETE /api/v1/accounts/{account}/inboxes/{inbox} | ✅ |
| GET /api/v1/accounts/:id/inboxes/:id/members | GET /api/v1/accounts/{account}/inboxes/{inbox}/members | ✅ |
| POST /api/v1/accounts/:id/inboxes/:id/members | POST /api/v1/accounts/{account}/inboxes/{inbox}/members | ✅ |
| DELETE /api/v1/accounts/:id/inboxes/:id/members | DELETE /api/v1/accounts/{account}/inboxes/{inbox}/members | ✅ |

### Teams

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/teams | GET /api/v1/accounts/{account}/teams | ✅ |
| POST /api/v1/accounts/:id/teams | POST /api/v1/accounts/{account}/teams | ✅ |
| GET /api/v1/accounts/:id/teams/:id | GET /api/v1/accounts/{account}/teams/{team} | ✅ |
| PATCH /api/v1/accounts/:id/teams/:id | PATCH /api/v1/accounts/{account}/teams/{team} | ✅ |
| DELETE /api/v1/accounts/:id/teams/:id | DELETE /api/v1/accounts/{account}/teams/{team} | ✅ |
| GET /api/v1/accounts/:id/teams/:id/members | GET /api/v1/accounts/{account}/teams/{team}/members | ✅ |
| POST /api/v1/accounts/:id/teams/:id/members | POST /api/v1/accounts/{account}/teams/{team}/members | ✅ |
| DELETE /api/v1/accounts/:id/teams/:id/members | DELETE /api/v1/accounts/{account}/teams/{team}/members | ✅ |

### Labels

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/labels | GET /api/v1/accounts/{account}/labels | ✅ |
| POST /api/v1/accounts/:id/labels | POST /api/v1/accounts/{account}/labels | ✅ |
| GET /api/v1/accounts/:id/labels/:id | GET /api/v1/accounts/{account}/labels/{label} | ✅ |
| PATCH /api/v1/accounts/:id/labels/:id | PATCH /api/v1/accounts/{account}/labels/{label} | ✅ |
| DELETE /api/v1/accounts/:id/labels/:id | DELETE /api/v1/accounts/{account}/labels/{label} | ✅ |

### Canned Responses

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/canned_responses | GET /api/v1/accounts/{account}/canned_responses | ✅ |
| POST /api/v1/accounts/:id/canned_responses | POST /api/v1/accounts/{account}/canned_responses | ✅ |
| PATCH /api/v1/accounts/:id/canned_responses/:id | PATCH /api/v1/accounts/{account}/canned_responses/{canned_response} | ✅ |
| DELETE /api/v1/accounts/:id/canned_responses/:id | DELETE /api/v1/accounts/{account}/canned_responses/{canned_response} | ✅ |

### Automation Rules

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/automation_rules | GET /api/v1/accounts/{account}/automation_rules | ✅ |
| POST /api/v1/accounts/:id/automation_rules | POST /api/v1/accounts/{account}/automation_rules | ✅ |
| GET /api/v1/accounts/:id/automation_rules/:id | GET /api/v1/accounts/{account}/automation_rules/{automation_rule} | ✅ |
| PATCH /api/v1/accounts/:id/automation_rules/:id | PATCH /api/v1/accounts/{account}/automation_rules/{automation_rule} | ✅ |
| DELETE /api/v1/accounts/:id/automation_rules/:id | DELETE /api/v1/accounts/{account}/automation_rules/{automation_rule} | ✅ |
| POST /api/v1/accounts/:id/automation_rules/:id/clone | POST /api/v1/accounts/{account}/automation_rules/{automation_rule}/clone | ✅ |

### Webhooks

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/webhooks | GET /api/v1/accounts/{account}/webhooks | ✅ |
| POST /api/v1/accounts/:id/webhooks | POST /api/v1/accounts/{account}/webhooks | ✅ |
| PATCH /api/v1/accounts/:id/webhooks/:id | PATCH /api/v1/accounts/{account}/webhooks/{webhook} | ✅ |
| DELETE /api/v1/accounts/:id/webhooks/:id | DELETE /api/v1/accounts/{account}/webhooks/{webhook} | ✅ |

### Agent Bots

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/agent_bots | GET /api/v1/accounts/{account}/agent_bots | ✅ |
| POST /api/v1/accounts/:id/agent_bots | POST /api/v1/accounts/{account}/agent_bots | ✅ |
| GET /api/v1/accounts/:id/agent_bots/:id | GET /api/v1/accounts/{account}/agent_bots/{agent_bot} | ✅ |
| PATCH /api/v1/accounts/:id/agent_bots/:id | PATCH /api/v1/accounts/{account}/agent_bots/{agent_bot} | ✅ |
| DELETE /api/v1/accounts/:id/agent_bots/:id | DELETE /api/v1/accounts/{account}/agent_bots/{agent_bot} | ✅ |

### Macros

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/macros | GET /api/v1/accounts/{account}/macros | ✅ |
| POST /api/v1/accounts/:id/macros | POST /api/v1/accounts/{account}/macros | ✅ |
| GET /api/v1/accounts/:id/macros/:id | GET /api/v1/accounts/{account}/macros/{macro} | ✅ |
| PATCH /api/v1/accounts/:id/macros/:id | PATCH /api/v1/accounts/{account}/macros/{macro} | ✅ |
| DELETE /api/v1/accounts/:id/macros/:id | DELETE /api/v1/accounts/{account}/macros/{macro} | ✅ |
| POST /api/v1/accounts/:id/macros/:id/execute | POST /api/v1/accounts/{account}/macros/{macro}/execute | ✅ |

### Custom Filters

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/custom_filters | GET /api/v1/accounts/{account}/custom_filters | ✅ |
| POST /api/v1/accounts/:id/custom_filters | POST /api/v1/accounts/{account}/custom_filters | ✅ |
| GET /api/v1/accounts/:id/custom_filters/:id | GET /api/v1/accounts/{account}/custom_filters/{custom_filter} | ✅ |
| PATCH /api/v1/accounts/:id/custom_filters/:id | PATCH /api/v1/accounts/{account}/custom_filters/{custom_filter} | ✅ |
| DELETE /api/v1/accounts/:id/custom_filters/:id | DELETE /api/v1/accounts/{account}/custom_filters/{custom_filter} | ✅ |

### Custom Attribute Definitions

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/custom_attribute_definitions | GET /api/v1/accounts/{account}/custom_attribute_definitions | ✅ |
| POST /api/v1/accounts/:id/custom_attribute_definitions | POST /api/v1/accounts/{account}/custom_attribute_definitions | ✅ |
| GET /api/v1/accounts/:id/custom_attribute_definitions/:id | GET /api/v1/accounts/{account}/custom_attribute_definitions/{custom_attribute_definition} | ✅ |
| PATCH /api/v1/accounts/:id/custom_attribute_definitions/:id | PATCH /api/v1/accounts/{account}/custom_attribute_definitions/{custom_attribute_definition} | ✅ |
| DELETE /api/v1/accounts/:id/custom_attribute_definitions/:id | DELETE /api/v1/accounts/{account}/custom_attribute_definitions/{custom_attribute_definition} | ✅ |

### Campaigns

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/campaigns | GET /api/v1/accounts/{account}/campaigns | ✅ |
| POST /api/v1/accounts/:id/campaigns | POST /api/v1/accounts/{account}/campaigns | ✅ |
| GET /api/v1/accounts/:id/campaigns/:id | GET /api/v1/accounts/{account}/campaigns/{campaign} | ✅ |
| PATCH /api/v1/accounts/:id/campaigns/:id | PATCH /api/v1/accounts/{account}/campaigns/{campaign} | ✅ |
| DELETE /api/v1/accounts/:id/campaigns/:id | DELETE /api/v1/accounts/{account}/campaigns/{campaign} | ✅ |

### Dashboard Apps

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/dashboard_apps | GET /api/v1/accounts/{account}/dashboard_apps | ✅ |
| POST /api/v1/accounts/:id/dashboard_apps | POST /api/v1/accounts/{account}/dashboard_apps | ✅ |
| GET /api/v1/accounts/:id/dashboard_apps/:id | GET /api/v1/accounts/{account}/dashboard_apps/{dashboard_app} | ✅ |
| PATCH /api/v1/accounts/:id/dashboard_apps/:id | PATCH /api/v1/accounts/{account}/dashboard_apps/{dashboard_app} | ✅ |
| DELETE /api/v1/accounts/:id/dashboard_apps/:id | DELETE /api/v1/accounts/{account}/dashboard_apps/{dashboard_app} | ✅ |

### Users & Agents

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/agents | GET /api/v1/accounts/{account}/agents | ✅ |
| GET /api/v1/accounts/:id/agents/:id | GET /api/v1/accounts/{account}/agents/{agent} | ✅ |
| PATCH /api/v1/accounts/:id/agents/:id | PATCH /api/v1/accounts/{account}/agents/{agent} | ✅ |
| DELETE /api/v1/accounts/:id/agents/:id | DELETE /api/v1/accounts/{account}/agents/{agent} | ✅ |

### Portals (Help Center)

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/portals | GET /api/v1/accounts/{account}/portals | ✅ |
| POST /api/v1/accounts/:id/portals | POST /api/v1/accounts/{account}/portals | ✅ |
| GET /api/v1/accounts/:id/portals/:id | GET /api/v1/accounts/{account}/portals/{portal} | ✅ |
| PATCH /api/v1/accounts/:id/portals/:id | PATCH /api/v1/accounts/{account}/portals/{portal} | ✅ |
| DELETE /api/v1/accounts/:id/portals/:id | DELETE /api/v1/accounts/{account}/portals/{portal} | ✅ |
| GET /api/v1/accounts/:id/portals/:id/articles | GET /api/v1/accounts/{account}/portals/{portal}/articles | ✅ |
| GET /api/v1/accounts/:id/portals/:id/categories | GET /api/v1/accounts/{account}/portals/{portal}/categories | ✅ |

### Articles

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/portals/:id/articles | GET /api/v1/accounts/{account}/portals/{portal}/articles | ✅ |
| POST /api/v1/accounts/:id/portals/:id/articles | POST /api/v1/accounts/{account}/portals/{portal}/articles | ✅ |
| GET /api/v1/accounts/:id/portals/:id/articles/:id | GET /api/v1/accounts/{account}/portals/{portal}/articles/{article} | ✅ |
| PATCH /api/v1/accounts/:id/portals/:id/articles/:id | PATCH /api/v1/accounts/{account}/portals/{portal}/articles/{article} | ✅ |
| DELETE /api/v1/accounts/:id/portals/:id/articles/:id | DELETE /api/v1/accounts/{account}/portals/{portal}/articles/{article} | ✅ |

### Categories

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/portals/:id/categories | GET /api/v1/accounts/{account}/portals/{portal}/categories | ✅ |
| POST /api/v1/accounts/:id/portals/:id/categories | POST /api/v1/accounts/{account}/portals/{portal}/categories | ✅ |
| PATCH /api/v1/accounts/:id/portals/:id/categories/:id | PATCH /api/v1/accounts/{account}/portals/{portal}/categories/{category} | ✅ |
| DELETE /api/v1/accounts/:id/portals/:id/categories/:id | DELETE /api/v1/accounts/{account}/portals/{portal}/categories/{category} | ✅ |

### CSAT Survey Responses

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/csat_survey_responses | GET /api/v1/accounts/{account}/csat_survey_responses | ✅ |
| GET /api/v1/accounts/:id/csat_survey_responses/:id | GET /api/v1/accounts/{account}/csat_survey_responses/{csat_survey_response} | ✅ |
| GET /api/v1/accounts/:id/csat_survey_responses/metrics | GET /api/v1/accounts/{account}/csat_survey_responses/metrics | ✅ |
| GET /api/v1/accounts/:id/csat_survey_responses/download | GET /api/v1/accounts/{account}/csat_survey_responses/download | ✅ |

### Segments

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/segments | GET /api/v1/accounts/{account}/segments | ✅ |
| POST /api/v1/accounts/:id/segments | POST /api/v1/accounts/{account}/segments | ✅ |
| GET /api/v1/accounts/:id/segments/:id | GET /api/v1/accounts/{account}/segments/{segment} | ✅ |
| PATCH /api/v1/accounts/:id/segments/:id | PATCH /api/v1/accounts/{account}/segments/{segment} | ✅ |
| DELETE /api/v1/accounts/:id/segments/:id | DELETE /api/v1/accounts/{account}/segments/{segment} | ✅ |
| GET /api/v1/accounts/:id/segments/:id/contacts | GET /api/v1/accounts/{account}/segments/{segment}/contacts | ✅ |
| GET /api/v1/accounts/:id/segments/:id/count | GET /api/v1/accounts/{account}/segments/{segment}/count | ✅ |

### Reports

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/reports | GET /api/v1/accounts/{account}/reports | ✅ |
| GET /api/v1/accounts/:id/reports/conversations | GET /api/v1/accounts/{account}/reports/conversations | ✅ |
| GET /api/v1/accounts/:id/reports/agents | GET /api/v1/accounts/{account}/reports/agents | ✅ |
| GET /api/v1/accounts/:id/reports/inboxes | GET /api/v1/accounts/{account}/reports/inboxes | ✅ |
| GET /api/v1/accounts/:id/reports/teams | GET /api/v1/accounts/{account}/reports/teams | ✅ |
| GET /api/v1/accounts/:id/reports/labels | GET /api/v1/accounts/{account}/reports/labels | ✅ |
| GET /api/v1/accounts/:id/reports/download | GET /api/v1/accounts/{account}/reports/download | ✅ |

### SLA Policies

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/sla_policies | GET /api/v1/accounts/{account}/sla_policies | ✅ |
| POST /api/v1/accounts/:id/sla_policies | POST /api/v1/accounts/{account}/sla_policies | ✅ |
| GET /api/v1/accounts/:id/sla_policies/:id | GET /api/v1/accounts/{account}/sla_policies/{sla_policy} | ✅ |
| PATCH /api/v1/accounts/:id/sla_policies/:id | PATCH /api/v1/accounts/{account}/sla_policies/{sla_policy} | ✅ |
| DELETE /api/v1/accounts/:id/sla_policies/:id | DELETE /api/v1/accounts/{account}/sla_policies/{sla_policy} | ✅ |
| GET /api/v1/accounts/:id/sla_policies/breaches | GET /api/v1/accounts/{account}/sla_policies/breaches | ✅ |
| GET /api/v1/accounts/:id/sla_policies/metrics | GET /api/v1/accounts/{account}/sla_policies/metrics | ✅ |

### Audit Logs

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/audit_logs | GET /api/v1/accounts/{account}/audit_logs | ✅ |
| GET /api/v1/accounts/:id/audit_logs/:id | GET /api/v1/accounts/{account}/audit_logs/{log} | ✅ |
| GET /api/v1/accounts/:id/audit_logs/summary | GET /api/v1/accounts/{account}/audit_logs/summary | ✅ |
| GET /api/v1/accounts/:id/audit_logs/download | GET /api/v1/accounts/{account}/audit_logs/download | ✅ |

### Working Hours

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/inboxes/:id/working_hours | GET /api/v1/accounts/{account}/inboxes/{inbox}/working_hours | ✅ |
| PUT /api/v1/accounts/:id/inboxes/:id/working_hours | PUT /api/v1/accounts/{account}/inboxes/{inbox}/working_hours | ✅ |
| GET /api/v1/accounts/:id/inboxes/:id/is_open | GET /api/v1/accounts/{account}/inboxes/{inbox}/is_open | ✅ |

### Search

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/search | GET /api/v1/accounts/{account}/search | ✅ |
| GET /api/v1/accounts/:id/search/conversations | GET /api/v1/accounts/{account}/search/conversations | ✅ |
| GET /api/v1/accounts/:id/search/contacts | GET /api/v1/accounts/{account}/search/contacts | ✅ |
| GET /api/v1/accounts/:id/search/messages | GET /api/v1/accounts/{account}/search/messages | ✅ |

### Bulk Actions

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/bulk_actions | POST /api/v1/accounts/{account}/bulk_actions/conversations | ✅ |
| DELETE /api/v1/accounts/:id/bulk_actions | DELETE /api/v1/accounts/{account}/bulk_actions/conversations | ✅ |

### Notifications

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/notifications | GET /api/v1/notifications | ✅ |
| GET /api/v1/notifications/unread_count | GET /api/v1/notifications/unread_count | ✅ |
| POST /api/v1/notifications/:id/read | POST /api/v1/notifications/{notification}/read | ✅ |
| POST /api/v1/notifications/read_all | POST /api/v1/notifications/read_all | ✅ |
| DELETE /api/v1/notifications/:id | DELETE /api/v1/notifications/{notification} | ✅ |

---

## Channel Integrations

### WhatsApp

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/whatsapp | POST /api/v1/accounts/{account}/channels/whatsapp | ✅ |
| PATCH /api/v1/accounts/:id/channels/whatsapp/:id | PATCH /api/v1/accounts/{account}/channels/whatsapp/{inbox} | ✅ |
| POST /api/v1/webhooks/whatsapp | POST /api/v1/webhooks/whatsapp | ✅ |
| GET /api/v1/webhooks/whatsapp (verify) | GET /api/v1/webhooks/whatsapp | ✅ |

### Facebook

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/facebook | POST /api/v1/accounts/{account}/channels/facebook | ✅ |
| PATCH /api/v1/accounts/:id/channels/facebook/:id | PATCH /api/v1/accounts/{account}/channels/facebook/{inbox} | ✅ |
| GET /api/v1/accounts/:id/channels/facebook/pages | GET /api/v1/accounts/{account}/channels/facebook/pages | ✅ |
| POST /api/v1/webhooks/facebook | POST /api/v1/webhooks/facebook | ✅ |

### Telegram

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/telegram | POST /api/v1/accounts/{account}/channels/telegram | ✅ |
| PATCH /api/v1/accounts/:id/channels/telegram/:id | PATCH /api/v1/accounts/{account}/channels/telegram/{inbox} | ✅ |
| POST /api/v1/accounts/:id/channels/telegram/bot_info | POST /api/v1/accounts/{account}/channels/telegram/bot_info | ✅ |
| POST /api/v1/webhooks/telegram/:id | POST /api/v1/webhooks/telegram/{inboxId} | ✅ |

### Twitter

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/twitter | POST /api/v1/accounts/{account}/channels/twitter | ✅ |
| PATCH /api/v1/accounts/:id/channels/twitter/:id | PATCH /api/v1/accounts/{account}/channels/twitter/{inbox} | ✅ |
| GET /api/v1/accounts/:id/channels/twitter/authorize | GET /api/v1/accounts/{account}/channels/twitter/authorize | ✅ |
| POST /api/v1/accounts/:id/channels/twitter/callback | POST /api/v1/accounts/{account}/channels/twitter/callback | ✅ |

### Email

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/email | POST /api/v1/accounts/{account}/channels/email | ✅ |
| PATCH /api/v1/accounts/:id/channels/email/:id | PATCH /api/v1/accounts/{account}/channels/email/{inbox} | ✅ |
| POST /api/v1/accounts/:id/channels/email/test_imap | POST /api/v1/accounts/{account}/channels/email/test_imap | ✅ |
| POST /api/v1/accounts/:id/channels/email/test_smtp | POST /api/v1/accounts/{account}/channels/email/test_smtp | ✅ |

### SMS

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/sms | POST /api/v1/accounts/{account}/channels/sms | ✅ |
| PATCH /api/v1/accounts/:id/channels/sms/:id | PATCH /api/v1/accounts/{account}/channels/sms/{inbox} | ✅ |
| GET /api/v1/accounts/:id/channels/sms/available_numbers | GET /api/v1/accounts/{account}/channels/sms/available_numbers | ✅ |

### Line

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/line | POST /api/v1/accounts/{account}/channels/line | ✅ |
| PATCH /api/v1/accounts/:id/channels/line/:id | PATCH /api/v1/accounts/{account}/channels/line/{inbox} | ✅ |

### Web Widget

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/web_widget | POST /api/v1/accounts/{account}/channels/web_widget | ✅ |
| PATCH /api/v1/accounts/:id/channels/web_widget/:id | PATCH /api/v1/accounts/{account}/channels/web_widget/{inbox} | ✅ |
| GET /api/v1/accounts/:id/channels/web_widget/:id/script | GET /api/v1/accounts/{account}/channels/web_widget/{inbox}/script | ✅ |

### API Channel

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/accounts/:id/channels/api | POST /api/v1/accounts/{account}/channels/api | ✅ |
| PATCH /api/v1/accounts/:id/channels/api/:id | PATCH /api/v1/accounts/{account}/channels/api/{inbox} | ✅ |
| POST /api/v1/accounts/:id/channels/api/:id/regenerate_key | POST /api/v1/accounts/{account}/channels/api/{inbox}/regenerate_key | ✅ |

---

## Third-Party Integrations

### Slack

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/integrations/slack | GET /api/v1/accounts/{account}/integrations/slack | ✅ |
| POST /api/v1/accounts/:id/integrations/slack | POST /api/v1/accounts/{account}/integrations/slack | ✅ |
| PATCH /api/v1/accounts/:id/integrations/slack | PATCH /api/v1/accounts/{account}/integrations/slack | ✅ |
| DELETE /api/v1/accounts/:id/integrations/slack | DELETE /api/v1/accounts/{account}/integrations/slack | ✅ |
| GET /api/v1/accounts/:id/integrations/slack/channels | GET /api/v1/accounts/{account}/integrations/slack/channels | ✅ |

### Dialogflow

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/integrations/dialogflow | GET /api/v1/accounts/{account}/integrations/dialogflow | ✅ |
| POST /api/v1/accounts/:id/integrations/dialogflow | POST /api/v1/accounts/{account}/integrations/dialogflow | ✅ |
| PATCH /api/v1/accounts/:id/integrations/dialogflow | PATCH /api/v1/accounts/{account}/integrations/dialogflow | ✅ |
| DELETE /api/v1/accounts/:id/integrations/dialogflow | DELETE /api/v1/accounts/{account}/integrations/dialogflow | ✅ |
| POST /api/v1/accounts/:id/integrations/dialogflow/test | POST /api/v1/accounts/{account}/integrations/dialogflow/test | ✅ |

### Linear

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/integrations/linear | GET /api/v1/accounts/{account}/integrations/linear | ✅ |
| POST /api/v1/accounts/:id/integrations/linear | POST /api/v1/accounts/{account}/integrations/linear | ✅ |
| PATCH /api/v1/accounts/:id/integrations/linear | PATCH /api/v1/accounts/{account}/integrations/linear | ✅ |
| DELETE /api/v1/accounts/:id/integrations/linear | DELETE /api/v1/accounts/{account}/integrations/linear | ✅ |
| GET /api/v1/accounts/:id/integrations/linear/teams | GET /api/v1/accounts/{account}/integrations/linear/teams | ✅ |
| GET /api/v1/accounts/:id/integrations/linear/projects | GET /api/v1/accounts/{account}/integrations/linear/projects | ✅ |
| POST /api/v1/accounts/:id/integrations/linear/issues | POST /api/v1/accounts/{account}/integrations/linear/issues | ✅ |
| POST /api/v1/accounts/:id/integrations/linear/issues/link | POST /api/v1/accounts/{account}/integrations/linear/issues/link | ✅ |
| POST /api/v1/accounts/:id/integrations/linear/issues/unlink | POST /api/v1/accounts/{account}/integrations/linear/issues/unlink | ✅ |

### OpenAI

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/integrations/openai | GET /api/v1/accounts/{account}/integrations/openai | ✅ |
| POST /api/v1/accounts/:id/integrations/openai | POST /api/v1/accounts/{account}/integrations/openai | ✅ |
| PATCH /api/v1/accounts/:id/integrations/openai | PATCH /api/v1/accounts/{account}/integrations/openai | ✅ |
| DELETE /api/v1/accounts/:id/integrations/openai | DELETE /api/v1/accounts/{account}/integrations/openai | ✅ |
| POST /api/v1/accounts/:id/integrations/openai/suggest | POST /api/v1/accounts/{account}/integrations/openai/suggest | ✅ |
| POST /api/v1/accounts/:id/integrations/openai/summarize | POST /api/v1/accounts/{account}/integrations/openai/summarize | ✅ |
| POST /api/v1/accounts/:id/integrations/openai/improve_tone | POST /api/v1/accounts/{account}/integrations/openai/improve_tone | ✅ |

### Shopify

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /api/v1/accounts/:id/integrations/shopify | GET /api/v1/accounts/{account}/integrations/shopify | ✅ |
| POST /api/v1/accounts/:id/integrations/shopify | POST /api/v1/accounts/{account}/integrations/shopify | ✅ |
| PATCH /api/v1/accounts/:id/integrations/shopify | PATCH /api/v1/accounts/{account}/integrations/shopify | ✅ |
| DELETE /api/v1/accounts/:id/integrations/shopify | DELETE /api/v1/accounts/{account}/integrations/shopify | ✅ |
| GET /api/v1/accounts/:id/integrations/shopify/contacts/:id/customer | GET /api/v1/accounts/{account}/integrations/shopify/contacts/{contact}/customer | ✅ |
| GET /api/v1/accounts/:id/integrations/shopify/contacts/:id/orders | GET /api/v1/accounts/{account}/integrations/shopify/contacts/{contact}/orders | ✅ |

---

## Super Admin APIs

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /super_admin/accounts | GET /api/v1/super_admin/accounts | ✅ |
| POST /super_admin/accounts | POST /api/v1/super_admin/accounts | ✅ |
| GET /super_admin/accounts/:id | GET /api/v1/super_admin/accounts/{account} | ✅ |
| PATCH /super_admin/accounts/:id | PATCH /api/v1/super_admin/accounts/{account} | ✅ |
| DELETE /super_admin/accounts/:id | DELETE /api/v1/super_admin/accounts/{account} | ✅ |
| POST /super_admin/accounts/:id/seed | POST /api/v1/super_admin/accounts/{account}/seed | ✅ |
| POST /super_admin/accounts/:id/reset_cache | POST /api/v1/super_admin/accounts/{account}/reset_cache | ✅ |
| GET /super_admin/users | GET /api/v1/super_admin/users | ✅ |
| POST /super_admin/users | POST /api/v1/super_admin/users | ✅ |
| GET /super_admin/users/:id | GET /api/v1/super_admin/users/{user} | ✅ |
| PATCH /super_admin/users/:id | PATCH /api/v1/super_admin/users/{user} | ✅ |
| DELETE /super_admin/users/:id | DELETE /api/v1/super_admin/users/{user} | ✅ |
| DELETE /super_admin/users/:id/avatar | DELETE /api/v1/super_admin/users/{user}/avatar | ✅ |
| GET /super_admin/agent_bots | GET /api/v1/super_admin/agent_bots | ✅ |
| POST /super_admin/agent_bots | POST /api/v1/super_admin/agent_bots | ✅ |
| GET /super_admin/agent_bots/:id | GET /api/v1/super_admin/agent_bots/{agentBot} | ✅ |
| PATCH /super_admin/agent_bots/:id | PATCH /api/v1/super_admin/agent_bots/{agentBot} | ✅ |
| DELETE /super_admin/agent_bots/:id | DELETE /api/v1/super_admin/agent_bots/{agentBot} | ✅ |
| GET /super_admin/platform_apps | GET /api/v1/super_admin/platform_apps | ✅ |
| POST /super_admin/platform_apps | POST /api/v1/super_admin/platform_apps | ✅ |
| GET /super_admin/platform_apps/:id | GET /api/v1/super_admin/platform_apps/{platformApp} | ✅ |
| PATCH /super_admin/platform_apps/:id | PATCH /api/v1/super_admin/platform_apps/{platformApp} | ✅ |
| DELETE /super_admin/platform_apps/:id | DELETE /api/v1/super_admin/platform_apps/{platformApp} | ✅ |
| POST /super_admin/platform_apps/:id/regenerate_token | POST /api/v1/super_admin/platform_apps/{platformApp}/regenerate_token | ✅ |
| GET /super_admin/instance_status | GET /api/v1/super_admin/instance_status | ✅ |
| GET /super_admin/installation_configs | GET /api/v1/super_admin/installation_configs | ✅ |
| POST /super_admin/installation_configs | POST /api/v1/super_admin/installation_configs | ✅ |
| GET /super_admin/installation_configs/groups | GET /api/v1/super_admin/installation_configs/groups | ✅ |
| GET /super_admin/installation_configs/group/:group | GET /api/v1/super_admin/installation_configs/group/{group} | ✅ |
| GET /super_admin/access_tokens | GET /api/v1/super_admin/access_tokens | ✅ |
| POST /super_admin/access_tokens | POST /api/v1/super_admin/access_tokens | ✅ |
| DELETE /super_admin/access_tokens/:id | DELETE /api/v1/super_admin/access_tokens/{accessToken} | ✅ |

---

## Widget API (Public Chat Widget)

The Widget API provides endpoints for the public chat widget embedded on customer websites.

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /api/v1/widget/config | POST /api/v1/widget/config | ✅ |
| GET /api/v1/widget/campaigns | GET /api/v1/widget/campaigns | ✅ |
| GET /api/v1/widget/contact | GET /api/v1/widget/contact | ✅ |
| PATCH /api/v1/widget/contact | PATCH /api/v1/widget/contact | ✅ |
| POST /api/v1/widget/contact/destroy_custom_attributes | POST /api/v1/widget/contact/destroy_custom_attributes | ✅ |
| PATCH /api/v1/widget/contact/set_user | PATCH /api/v1/widget/contact/set_user | ✅ |
| GET /api/v1/widget/conversations | GET /api/v1/widget/conversations | ✅ |
| POST /api/v1/widget/conversations | POST /api/v1/widget/conversations | ✅ |
| GET /api/v1/widget/conversations/toggle_status | GET /api/v1/widget/conversations/toggle_status | ✅ |
| POST /api/v1/widget/conversations/toggle_typing | POST /api/v1/widget/conversations/toggle_typing | ✅ |
| POST /api/v1/widget/conversations/update_last_seen | POST /api/v1/widget/conversations/update_last_seen | ✅ |
| POST /api/v1/widget/conversations/set_custom_attributes | POST /api/v1/widget/conversations/set_custom_attributes | ✅ |
| POST /api/v1/widget/conversations/destroy_custom_attributes | POST /api/v1/widget/conversations/destroy_custom_attributes | ✅ |
| POST /api/v1/widget/conversations/transcript | POST /api/v1/widget/conversations/transcript | ✅ |
| GET /api/v1/widget/messages | GET /api/v1/widget/messages | ✅ |
| POST /api/v1/widget/messages | POST /api/v1/widget/messages | ✅ |
| PATCH /api/v1/widget/messages/:id | PATCH /api/v1/widget/messages/{message} | ✅ |
| GET /api/v1/widget/inbox_members | GET /api/v1/widget/inbox_members | ✅ |
| POST /api/v1/widget/labels | POST /api/v1/widget/labels | ✅ |
| DELETE /api/v1/widget/labels/:id | DELETE /api/v1/widget/labels/{label} | ✅ |
| POST /api/v1/widget/events | POST /api/v1/widget/events | ✅ |
| POST /api/v1/widget/direct_uploads | POST /api/v1/widget/direct_uploads | ✅ |

---

## Platform API

The Platform API provides endpoints for platform-level integrations and SSO.

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| GET /platform/api/v1/users/:id | GET /api/v1/platform/users/{user} | ✅ |
| POST /platform/api/v1/users | POST /api/v1/platform/users | ✅ |
| PATCH /platform/api/v1/users/:id | PATCH /api/v1/platform/users/{user} | ✅ |
| DELETE /platform/api/v1/users/:id | DELETE /api/v1/platform/users/{user} | ✅ |
| GET /platform/api/v1/users/:id/login | GET /api/v1/platform/users/{user}/login | ✅ |
| POST /platform/api/v1/users/:id/token | POST /api/v1/platform/users/{user}/token | ✅ |
| GET /platform/api/v1/accounts | GET /api/v1/platform/accounts | ✅ |
| GET /platform/api/v1/accounts/:id | GET /api/v1/platform/accounts/{account} | ✅ |
| POST /platform/api/v1/accounts | POST /api/v1/platform/accounts | ✅ |
| PATCH /platform/api/v1/accounts/:id | PATCH /api/v1/platform/accounts/{account} | ✅ |
| DELETE /platform/api/v1/accounts/:id | DELETE /api/v1/platform/accounts/{account} | ✅ |
| GET /platform/api/v1/accounts/:id/account_users | GET /api/v1/platform/accounts/{account}/account_users | ✅ |
| POST /platform/api/v1/accounts/:id/account_users | POST /api/v1/platform/accounts/{account}/account_users | ✅ |
| DELETE /platform/api/v1/accounts/:id/account_users | DELETE /api/v1/platform/accounts/{account}/account_users | ✅ |
| GET /platform/api/v1/agent_bots | GET /api/v1/platform/agent_bots | ✅ |
| GET /platform/api/v1/agent_bots/:id | GET /api/v1/platform/agent_bots/{agentBot} | ✅ |
| POST /platform/api/v1/agent_bots | POST /api/v1/platform/agent_bots | ✅ |
| PATCH /platform/api/v1/agent_bots/:id | PATCH /api/v1/platform/agent_bots/{agentBot} | ✅ |
| DELETE /platform/api/v1/agent_bots/:id | DELETE /api/v1/platform/agent_bots/{agentBot} | ✅ |
| DELETE /platform/api/v1/agent_bots/:id/avatar | DELETE /api/v1/platform/agent_bots/{agentBot}/avatar | ✅ |

---

## Public API (Inbox-based)

The Public API provides endpoints for contacts to interact with inboxes directly.

| Chatwoot Rails Endpoint | ClearLine Laravel Endpoint | Status |
|------------------------|---------------------------|--------|
| POST /public/api/v1/inboxes/:inbox/contacts | POST /api/v1/public/inboxes/{inbox}/contacts | ✅ |
| GET /public/api/v1/inboxes/:inbox/contacts/:id | GET /api/v1/public/inboxes/{inbox}/contacts/{contact} | ✅ |
| PATCH /public/api/v1/inboxes/:inbox/contacts/:id | PATCH /api/v1/public/inboxes/{inbox}/contacts/{contact} | ✅ |
| GET /public/api/v1/inboxes/:inbox/contacts/:id/conversations | GET /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations | ✅ |
| POST /public/api/v1/inboxes/:inbox/contacts/:id/conversations | POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations | ✅ |
| GET /public/api/v1/inboxes/:inbox/contacts/:id/conversations/:id | GET /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation} | ✅ |
| POST /public/api/v1/inboxes/:inbox/contacts/:id/conversations/:id/toggle_status | POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/toggle_status | ✅ |
| POST /public/api/v1/inboxes/:inbox/contacts/:id/conversations/:id/toggle_typing | POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/toggle_typing | ✅ |
| POST /public/api/v1/inboxes/:inbox/contacts/:id/conversations/:id/update_last_seen | POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/update_last_seen | ✅ |
| GET /public/api/v1/inboxes/:inbox/contacts/:id/conversations/:id/messages | GET /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/messages | ✅ |
| POST /public/api/v1/inboxes/:inbox/contacts/:id/conversations/:id/messages | POST /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/messages | ✅ |
| PATCH /public/api/v1/inboxes/:inbox/contacts/:id/conversations/:id/messages/:id | PATCH /api/v1/public/inboxes/{inbox}/contacts/{contact}/conversations/{conversation}/messages/{message} | ✅ |

---

## Summary

| Category | Chatwoot Rails | ClearLine Laravel | Status |
|----------|---------------|-------------------|--------|
| Core Resources | 150+ | 150+ | ✅ |
| Channel Integrations | 9 | 9 | ✅ |
| Third-Party Integrations | 5 | 5 | ✅ |
| Super Admin APIs | 25+ | 25+ | ✅ |
| Widget API | 20+ | 20+ | ✅ |
| Platform API | 15+ | 15+ | ✅ |
| Public API | 12+ | 12+ | ✅ |
| WebSocket Broadcasting | Sidekiq + ActionCable | Laravel Reverb | ✅ |
| Queue Processing | Sidekiq | Laravel Horizon | ✅ |
| Authentication | Devise | Laravel Sanctum | ✅ |

**Total API Migration: 100% Complete**

---

## Architecture Comparison

| Aspect | Chatwoot (Rails) | ClearLine (Laravel) |
|--------|-----------------|---------------------|
| Framework | Ruby on Rails 7 | Laravel 12 |
| Database | PostgreSQL | PostgreSQL |
| Cache | Redis | Redis |
| Queue | Sidekiq | Laravel Horizon |
| WebSocket | ActionCable | Laravel Reverb |
| Authentication | Devise | Laravel Sanctum |
| Authorization | Pundit | Spatie Permission |
| Activity Log | N/A | Spatie Activity Log |
| Actions | Service Objects | Lorisleiva Actions |
| DTOs | N/A | Spatie Data |
| Testing | RSpec | Pest PHP |

---

**Last Updated:** 2025-12-26
**Document Version:** 1.0.0
