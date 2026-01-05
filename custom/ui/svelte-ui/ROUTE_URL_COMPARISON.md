# Vue Frontend vs Svelte-UI SPA Route URL Comparison

This document provides a comprehensive inventory of every Vue frontend route (dashboard, V3 auth shell, and widget) and compares them with the current Svelte-UI SPA routes. All paths are shown with their exact dynamic segments (`:accountId`, `:id`, etc.) to ensure complete coverage.

## Source files analyzed
- **Vue dashboard routes**: `app/javascript/dashboard/routes/**/*.routes.js` (25+ route files)
- **Vue V3 auth shell**: `app/javascript/v3/views/routes.js` (6 auth routes)
- **Vue widget shell**: `app/javascript/widget/router.js` (6 widget routes)
- **Svelte SPA routes**: `custom/ui/svelte-ui/src/routes/**/+page.*` (50+ file-based routes)

## Complete Vue → Svelte route coverage analysis

| Area | Vue route URLs | Svelte SPA route URLs | Coverage Status |
| --- | --- | --- | --- |
| **Auth (V3 shell)** | `/app/login`, `/app/login/sso`, `/app/auth/signup`, `/app/auth/confirmation`, `/app/auth/password/edit`, `/app/auth/reset/password` | `/login`, `/auth/login`, `/app/login`, `/auth/register`, `/app/auth/signup` | **Partial** - Missing SSO, confirmation, password reset flows |
| **Account shell & guards** | `/app/accounts/:accountId`, `/app/accounts/:accountId/suspended`, `/app/no-accounts` | `/app/accounts/:accountId`, `/app/unauthorized` | **Partial** - Generic unauthorized instead of specific suspended/no-accounts |
| **Conversations** | `/app/accounts/:accountId/dashboard`, `/app/accounts/:accountId/conversations/:conversation_id`, `/app/accounts/:accountId/inbox/:inbox_id`, `/app/accounts/:accountId/inbox/:inbox_id/conversations/:conversation_id`, `/app/accounts/:accountId/label/:label`, `/app/accounts/:accountId/label/:label/conversations/:conversation_id`, `/app/accounts/:accountId/team/:teamId`, `/app/accounts/:accountId/team/:teamId/conversations/:conversationId`, `/app/accounts/:accountId/custom_view/:id`, `/app/accounts/:accountId/custom_view/:id/conversations/:conversation_id`, `/app/accounts/:accountId/mentions/conversations`, `/app/accounts/:accountId/mentions/conversations/:conversationId`, `/app/accounts/:accountId/unattended/conversations`, `/app/accounts/:accountId/unattended/conversations/:conversationId`, `/app/accounts/:accountId/participating/conversations`, `/app/accounts/:accountId/participating/conversations/:conversationId` | `/app/accounts/:accountId/conversations`, `/app/accounts/:accountId/conversations/:id` | **Basic** - Only list/detail, missing all filter routes (inbox, label, team, custom-view, mentions, participating, unattended) |
| **Inbox view** | `/app/accounts/:accountId/inbox-view`, `/app/accounts/:accountId/inbox-view/:type/:id` | — | **Missing** - No dedicated inbox-view shell |
| **Contacts** | `/app/accounts/:accountId/contacts`, `/app/accounts/:accountId/contacts/segments/:segmentId`, `/app/accounts/:accountId/contacts/labels/:label`, `/app/accounts/:accountId/contacts/active`, `/app/accounts/:accountId/contacts/:contactId`, `/app/accounts/:accountId/contacts/:contactId/segments/:segmentId`, `/app/accounts/:accountId/contacts/:contactId/labels/:label` | `/app/accounts/:accountId/contacts` | **Basic** - Single page, missing segment/label filters and edit paths |
| **Companies** | `/app/accounts/:accountId/companies` | `/app/accounts/:accountId/companies` | **Complete** - Full parity |
| **Campaigns** | `/app/accounts/:accountId/campaigns` (redirect), `/app/accounts/:accountId/campaigns/ongoing`, `/app/accounts/:accountId/campaigns/one_off`, `/app/accounts/:accountId/campaigns/live_chat`, `/app/accounts/:accountId/campaigns/sms`, `/app/accounts/:accountId/campaigns/whatsapp` | `/app/accounts/:accountId/campaigns` | **Basic** - Single page instead of per-type routes |
| **Captain (AI workspace)** | `/app/accounts/:accountId/captain`, `/app/accounts/:accountId/captain/:navigationPath`, `/app/accounts/:accountId/captain/assistants`, `/app/accounts/:accountId/captain/:assistantId/faqs`, `/app/accounts/:accountId/captain/:assistantId/faqs/pending`, `/app/accounts/:accountId/captain/:assistantId/documents`, `/app/accounts/:accountId/captain/:assistantId/tools`, `/app/accounts/:accountId/captain/:assistantId/scenarios`, `/app/accounts/:accountId/captain/:assistantId/playground`, `/app/accounts/:accountId/captain/:assistantId/inboxes`, `/app/accounts/:accountId/captain/:assistantId/settings`, `/app/accounts/:accountId/captain/:assistantId/settings/guardrails`, `/app/accounts/:accountId/captain/:assistantId/settings/guidelines` | — | **Missing** - Entire AI workspace not implemented |
| **Notifications** | `/app/accounts/:accountId/notifications` | `/app/accounts/:accountId/settings/notifications` | **Relocated** - Moved under settings |
| **Search** | `/app/accounts/:accountId/search/:tab?` | — | **Missing** - No search functionality |
| **Help Center (portals)** | `/app/accounts/:accountId/portals`, `/app/accounts/:accountId/portals/:portalSlug/:locale/:categorySlug?/articles/:tab?`, `/app/accounts/:accountId/portals/:portalSlug/:locale/:categorySlug?/articles/new`, `/app/accounts/:accountId/portals/:portalSlug/:locale/:categorySlug?/articles/:tab?/edit/:articleSlug`, `/app/accounts/:accountId/portals/:portalSlug/:locale/categories`, `/app/accounts/:accountId/portals/:portalSlug/:locale/categories/:categorySlug/articles`, `/app/accounts/:accountId/portals/:portalSlug/:locale/categories/:categorySlug/articles/:articleSlug`, `/app/accounts/:accountId/portals/:portalSlug/locales`, `/app/accounts/:accountId/portals/:portalSlug/settings`, `/app/accounts/:accountId/portals/new`, `/app/accounts/:accountId/portals/:navigationPath` | `/portal`, `/portal/articles/:slug`, `/portal/categories/:slug` | **Different** - Public-scoped instead of account-scoped |
| **Reports** | `/app/accounts/:accountId/reports` (redirect), `/app/accounts/:accountId/reports/overview`, `/app/accounts/:accountId/reports/conversation`, `/app/accounts/:accountId/reports/agent`, `/app/accounts/:accountId/reports/inboxes`, `/app/accounts/:accountId/reports/label`, `/app/accounts/:accountId/reports/teams`, `/app/accounts/:accountId/reports/agents_overview`, `/app/accounts/:accountId/reports/agents/:id`, `/app/accounts/:accountId/reports/inboxes_overview`, `/app/accounts/:accountId/reports/inboxes/:id`, `/app/accounts/:accountId/reports/teams_overview`, `/app/accounts/:accountId/reports/teams/:id`, `/app/accounts/:accountId/reports/labels_overview`, `/app/accounts/:accountId/reports/labels/:id`, `/app/accounts/:accountId/reports/sla`, `/app/accounts/:accountId/reports/csat`, `/app/accounts/:accountId/reports/bot` | `/app/accounts/:accountId/reports` | **Basic** - Single page instead of detailed sub-reports |
| **Settings – landing** | `/app/accounts/:accountId/settings` (redirect to role-specific target) | `/app/accounts/:accountId/settings` | **Complete** - Both provide settings entry |
| **Settings – general** | `/app/accounts/:accountId/settings/general` | `/app/accounts/:accountId/settings/account` | **Complete** - Different path, same functionality |
| **Settings – agents** | `/app/accounts/:accountId/settings/agents`, `/app/accounts/:accountId/settings/agents/list` | `/app/accounts/:accountId/settings/agents` | **Complete** - Single page vs list view |
| **Settings – assignment policy** | `/app/accounts/:accountId/settings/assignment-policy`, `/app/accounts/:accountId/settings/assignment-policy/index`, `/app/accounts/:accountId/settings/assignment-policy/assignment`, `/app/accounts/:accountId/settings/assignment-policy/assignment/create`, `/app/accounts/:accountId/settings/assignment-policy/assignment/edit/:id`, `/app/accounts/:accountId/settings/assignment-policy/capacity`, `/app/accounts/:accountId/settings/assignment-policy/capacity/create`, `/app/accounts/:accountId/settings/assignment-policy/capacity/edit/:id` | — | **Missing** - Assignment policy not implemented |
| **Settings – agent bots** | `/app/accounts/:accountId/settings/agent-bots` | `/app/super_admin/agent-bots`, `/app/super_admin/agent-bots/:id`, `/app/super_admin/agent-bots/new` | **Relocated** - Moved to super-admin scope |
| **Settings – custom attributes** | `/app/accounts/:accountId/settings/custom-attributes`, `/app/accounts/:accountId/settings/custom-attributes/list` | `/app/accounts/:accountId/settings/attributes` | **Complete** - Single page implementation |
| **Settings – automation** | `/app/accounts/:accountId/settings/automation`, `/app/accounts/:accountId/settings/automation/list` | `/app/accounts/:accountId/settings/automation` | **Complete** - Single page implementation |
| **Settings – audit logs** | `/app/accounts/:accountId/settings/audit-logs`, `/app/accounts/:accountId/settings/audit-logs/list` | `/app/accounts/:accountId/settings/audit-logs` | **Complete** - Single page implementation |
| **Settings – billing** | `/app/accounts/:accountId/settings/billing` | `/app/accounts/:accountId/settings/billing` | **Complete** - Full parity |
| **Settings – canned responses** | `/app/accounts/:accountId/settings/canned-response`, `/app/accounts/:accountId/settings/canned-response/list` | `/app/accounts/:accountId/canned-responses` | **Relocated** - Moved outside settings |
| **Settings – inboxes** | `/app/accounts/:accountId/settings/inboxes`, `/app/accounts/:accountId/settings/inboxes/list`, `/app/accounts/:accountId/settings/inboxes/new`, `/app/accounts/:accountId/settings/inboxes/new/:inbox_id/finish`, `/app/accounts/:accountId/settings/inboxes/new/:sub_page`, `/app/accounts/:accountId/settings/inboxes/new/:inbox_id/agents`, `/app/accounts/:accountId/settings/inboxes/:inboxId/:tab?` | `/app/accounts/:accountId/settings/inboxes`, `/app/accounts/:accountId/settings/inboxes/new`, `/app/accounts/:accountId/settings/inboxes/:id` | **Simplified** - Missing wizard sub-pages and tabs |
| **Settings – integrations** | `/app/accounts/:accountId/settings/integrations`, `/app/accounts/:accountId/settings/integrations/dashboard_apps`, `/app/accounts/:accountId/settings/integrations/webhook`, `/app/accounts/:accountId/settings/integrations/slack`, `/app/accounts/:accountId/settings/integrations/linear`, `/app/accounts/:accountId/settings/integrations/notion`, `/app/accounts/:accountId/settings/integrations/shopify`, `/app/accounts/:accountId/settings/integrations/:integration_id` | `/app/accounts/:accountId/integrations` | **Relocated & Simplified** - Moved outside settings, single page |
| **Settings – labels** | `/app/accounts/:accountId/settings/labels`, `/app/accounts/:accountId/settings/labels/list` | `/app/accounts/:accountId/labels` | **Relocated** - Moved to top-level |
| **Settings – macros** | `/app/accounts/:accountId/settings/macros`, `/app/accounts/:accountId/settings/macros/:macroId/edit`, `/app/accounts/:accountId/settings/macros/new` | `/app/accounts/:accountId/settings/macros` | **Simplified** - Edit/new handled in single page |
| **Settings – SLA** | `/app/accounts/:accountId/settings/sla`, `/app/accounts/:accountId/settings/sla/list` | `/app/accounts/:accountId/settings/sla` | **Complete** - Single page implementation |
| **Settings – teams** | `/app/accounts/:accountId/settings/teams`, `/app/accounts/:accountId/settings/teams/list`, `/app/accounts/:accountId/settings/teams/new`, `/app/accounts/:accountId/settings/teams/new/:teamId/finish`, `/app/accounts/:accountId/settings/teams/new/:teamId/agents`, `/app/accounts/:accountId/settings/teams/:teamId/edit`, `/app/accounts/:accountId/settings/teams/:teamId/edit/agents`, `/app/accounts/:accountId/settings/teams/:teamId/edit/finish` | `/app/accounts/:accountId/team` | **Relocated & Simplified** - Moved to top-level, single page |
| **Settings – custom roles** | `/app/accounts/:accountId/settings/custom-roles`, `/app/accounts/:accountId/settings/custom-roles/list` | — | **Missing** - Custom roles not implemented |
| **Settings – profile** | `/app/accounts/:accountId/profile/settings`, `/app/accounts/:accountId/profile/mfa` | `/app/accounts/:accountId/settings/profile` | **Relocated & Combined** - Moved under settings, MFA combined |
| **Settings – security** | `/app/accounts/:accountId/settings/security` | — | **Missing** - Security (SAML) settings not implemented |
| **Widget shell** | `/#/` (home), `/#/prechat-form`, `/#/messages`, `/#/article`, `/#/unread-messages`, `/#/campaigns` | `/widget` | **Simplified** - Single preview page instead of hash routes |

## Complete Svelte route inventory with Vue counterparts

| Svelte SPA route URL | Vue counterpart(s) | Implementation Status |
| --- | --- | --- |
| **Root & Landing** | | |
| `/` | — | **Svelte-only** - Landing page |
| **Authentication** | | |
| `/login` | `/app/login` | **Complete** - Data loader mirrors login |
| `/auth/login` | `/app/login` | **Complete** - Direct auth login |
| `/auth/register` | `/app/auth/signup` | **Complete** - Registration flow |
| `/app/login` | `/app/login` | **Complete** - App shell parity |
| `/app/auth/signup` | `/app/auth/signup` | **Complete** - Signup path |
| **Utility Pages** | | |
| `/onboarding` | — | **Svelte-only** - Onboarding helper |
| `/unauthorized` | `/app/accounts/:accountId/suspended`, `/app/no-accounts` | **Generic** - Covers multiple Vue scenarios |
| **Public Portal** | | |
| `/portal` | `/app/accounts/:accountId/portals/:navigationPath` | **Different scope** - Public vs account-scoped |
| `/portal/articles/:slug` | `/app/accounts/:accountId/portals/.../articles/:tab?` | **Different scope** - Public article display |
| `/portal/categories/:slug` | `/app/accounts/:accountId/portals/:portalSlug/:locale/categories` | **Different scope** - Public category display |
| **Development** | | |
| `/ui/:name` | — | **Svelte-only** - Component catalog |
| `/widget` | `/#/`, `/#/messages`, etc. | **Simplified** - Unified widget preview |
| **Survey** | | |
| `/survey` | — | **Svelte-only** - Public survey |
| `/survey/thank-you` | — | **Svelte-only** - Survey confirmation |
| **App Shell** | | |
| `/app` | `/app/accounts/:accountId` | **Entry point** - Vue requires immediate account context |
| `/app/accounts/:accountId` | `/app/accounts/:accountId` | **Complete** - Account home |
| **Account-Level Features** | | |
| `/app/accounts/:accountId/conversations` | `/app/accounts/:accountId/dashboard` | **Basic** - Conversation list only |
| `/app/accounts/:accountId/conversations/:id` | `/app/accounts/:accountId/conversations/:conversation_id` | **Complete** - Conversation detail |
| `/app/accounts/:accountId/contacts` | `/app/accounts/:accountId/contacts` | **Basic** - List only, no filters |
| `/app/accounts/:accountId/companies` | `/app/accounts/:accountId/companies` | **Complete** - Full parity |
| `/app/accounts/:accountId/campaigns` | `/app/accounts/:accountId/campaigns/*` | **Basic** - Single page vs type-specific |
| `/app/accounts/:accountId/integrations` | `/app/accounts/:accountId/settings/integrations/*` | **Relocated** - Outside settings |
| `/app/accounts/:accountId/reports` | `/app/accounts/:accountId/reports/*` | **Basic** - Single page vs detailed reports |
| `/app/accounts/:accountId/labels` | `/app/accounts/:accountId/settings/labels` | **Relocated** - Top-level vs settings |
| `/app/accounts/:accountId/canned-responses` | `/app/accounts/:accountId/settings/canned-response` | **Relocated** - Outside settings |
| `/app/accounts/:accountId/team` | `/app/accounts/:accountId/settings/teams` | **Relocated & Simplified** - Top-level, single page |
| **Settings** | | |
| `/app/accounts/:accountId/settings` | `/app/accounts/:accountId/settings` | **Complete** - Settings landing |
| `/app/accounts/:accountId/settings/account` | `/app/accounts/:accountId/settings/general` | **Complete** - General settings |
| `/app/accounts/:accountId/settings/inboxes` | `/app/accounts/:accountId/settings/inboxes` | **Complete** - Inbox list |
| `/app/accounts/:accountId/settings/inboxes/new` | `/app/accounts/:accountId/settings/inboxes/new` | **Simplified** - No wizard steps |
| `/app/accounts/:accountId/settings/inboxes/:id` | `/app/accounts/:accountId/settings/inboxes/:inboxId/:tab?` | **Simplified** - No tab parameters |
| `/app/accounts/:accountId/settings/notifications` | `/app/accounts/:accountId/notifications` | **Relocated** - Under settings |
| `/app/accounts/:accountId/settings/automation` | `/app/accounts/:accountId/settings/automation/list` | **Complete** - Automation rules |
| `/app/accounts/:accountId/settings/sla` | `/app/accounts/:accountId/settings/sla/list` | **Complete** - SLA policies |
| `/app/accounts/:accountId/settings/profile` | `/app/accounts/:accountId/profile/*` | **Relocated & Combined** - Profile + MFA |
| `/app/accounts/:accountId/settings/audit-logs` | `/app/accounts/:accountId/settings/audit-logs/list` | **Complete** - Audit logs |
| `/app/accounts/:accountId/settings/billing` | `/app/accounts/:accountId/settings/billing` | **Complete** - Billing |
| `/app/accounts/:accountId/settings/macros` | `/app/accounts/:accountId/settings/macros/*` | **Simplified** - Edit/new in single page |
| `/app/accounts/:accountId/settings/attributes` | `/app/accounts/:accountId/settings/custom-attributes/list` | **Complete** - Custom attributes |
| `/app/accounts/:accountId/settings/agents` | `/app/accounts/:accountId/settings/agents/list` | **Complete** - Agent management |
| **Super Admin** | | |
| `/app/super_admin/dashboard` | — | **Svelte-only** - Super admin dashboard |
| `/app/super_admin/settings` | — | **Svelte-only** - Super admin settings |
| `/app/super_admin/platform-apps` | — | **Svelte-only** - Platform apps list |
| `/app/super_admin/platform-apps/:id` | — | **Svelte-only** - Platform app detail |
| `/app/super_admin/accounts` | — | **Svelte-only** - Accounts management |
| `/app/super_admin/accounts/new` | — | **Svelte-only** - Account creation |
| `/app/super_admin/accounts/:id` | — | **Svelte-only** - Account detail |
| `/app/super_admin/agent-bots` | `/app/accounts/:accountId/settings/agent-bots` | **Relocated** - Global vs per-account |
| `/app/super_admin/agent-bots/new` | `/app/accounts/:accountId/settings/agent-bots` | **Relocated** - Creation flow |
| `/app/super_admin/agent-bots/:id` | `/app/accounts/:accountId/settings/agent-bots` | **Relocated** - Detail view |
| `/app/super_admin/users` | — | **Svelte-only** - User management |
| `/app/super_admin/users/new` | — | **Svelte-only** - User creation |
| `/app/super_admin/users/:id` | — | **Svelte-only** - User detail |
| **Error Handling** | | |
| `/app/unauthorized` | `/app/accounts/:accountId/suspended`, `/app/no-accounts` | **Generic** - Covers multiple scenarios |

## Missing Vue routes in Svelte (Implementation gaps)

### Major Missing Features
1. **Captain (AI Workspace)** - Entire feature missing (13 routes)
2. **Search functionality** - No search implementation
3. **Assignment Policy** - Complete feature missing (8 routes)
4. **Custom Roles** - Not implemented
5. **Security (SAML)** - Not implemented
6. **Inbox View Shell** - No dedicated inbox view

### Missing Auth Flows
1. **SSO Login** - `/app/login/sso`
2. **Email Confirmation** - `/app/auth/confirmation`
3. **Password Reset** - `/app/auth/password/edit`, `/app/auth/reset/password`

### Missing Conversation Filters
1. **Inbox filtering** - `/app/accounts/:accountId/inbox/:inbox_id`
2. **Label filtering** - `/app/accounts/:accountId/label/:label`
3. **Team filtering** - `/app/accounts/:accountId/team/:teamId`
4. **Custom view filtering** - `/app/accounts/:accountId/custom_view/:id`
5. **Mentions** - `/app/accounts/:accountId/mentions/conversations`
6. **Unattended** - `/app/accounts/:accountId/unattended/conversations`
7. **Participating** - `/app/accounts/:accountId/participating/conversations`

### Missing Contact Features
1. **Segment filtering** - `/app/accounts/:accountId/contacts/segments/:segmentId`
2. **Label filtering** - `/app/accounts/:accountId/contacts/labels/:label`
3. **Active contacts** - `/app/accounts/:accountId/contacts/active`
4. **Contact editing** - `/app/accounts/:accountId/contacts/:contactId`

### Missing Campaign Types
1. **Ongoing campaigns** - `/app/accounts/:accountId/campaigns/ongoing`
2. **One-off campaigns** - `/app/accounts/:accountId/campaigns/one_off`
3. **Live chat campaigns** - `/app/accounts/:accountId/campaigns/live_chat`
4. **SMS campaigns** - `/app/accounts/:accountId/campaigns/sms`
5. **WhatsApp campaigns** - `/app/accounts/:accountId/campaigns/whatsapp`

### Missing Report Types
1. **Overview reports** - `/app/accounts/:accountId/reports/overview`
2. **Conversation reports** - `/app/accounts/:accountId/reports/conversation`
3. **Agent reports** - `/app/accounts/:accountId/reports/agent`
4. **Inbox reports** - `/app/accounts/:accountId/reports/inboxes`
5. **Label reports** - `/app/accounts/:accountId/reports/label`
6. **Team reports** - `/app/accounts/:accountId/reports/teams`
7. **Detailed agent reports** - `/app/accounts/:accountId/reports/agents/:id`
8. **Detailed inbox reports** - `/app/accounts/:accountId/reports/inboxes/:id`
9. **Detailed team reports** - `/app/accounts/:accountId/reports/teams/:id`
10. **Detailed label reports** - `/app/accounts/:accountId/reports/labels/:id`
11. **SLA reports** - `/app/accounts/:accountId/reports/sla`
12. **CSAT reports** - `/app/accounts/:accountId/reports/csat`
13. **Bot reports** - `/app/accounts/:accountId/reports/bot`

### Missing Integration Types
1. **Dashboard apps** - `/app/accounts/:accountId/settings/integrations/dashboard_apps`
2. **Webhooks** - `/app/accounts/:accountId/settings/integrations/webhook`
3. **Slack integration** - `/app/accounts/:accountId/settings/integrations/slack`
4. **Linear integration** - `/app/accounts/:accountId/settings/integrations/linear`
5. **Notion integration** - `/app/accounts/:accountId/settings/integrations/notion`
6. **Shopify integration** - `/app/accounts/:accountId/settings/integrations/shopify`
7. **Generic integrations** - `/app/accounts/:accountId/settings/integrations/:integration_id`

### Missing Multi-step Wizards
1. **Inbox creation wizard** - Multiple sub-steps for channel setup and agent assignment
2. **Team creation wizard** - Multi-step team setup with agent assignment
3. **Team editing wizard** - Multi-step team editing flows

### Missing Portal Management (Account-scoped)
1. **Portal management** - `/app/accounts/:accountId/portals`
2. **Article management** - Complex nested routes for article CRUD
3. **Category management** - Category-specific article management
4. **Locale management** - `/app/accounts/:accountId/portals/:portalSlug/locales`
5. **Portal settings** - `/app/accounts/:accountId/portals/:portalSlug/settings`

## Route Architecture Differences

| Aspect | Vue Router | SvelteKit |
|--------|------------|-----------|
| **Route Definition** | Explicit route objects | File-based routing |
| **Dynamic Segments** | `:paramName` | `[paramName]` |
| **Nested Routes** | `children` array | Directory structure |
| **Route Guards** | `beforeEach` hooks, `meta` properties | Load functions |
| **Redirects** | `redirect` property | Load function redirects |
| **Multi-step Wizards** | Nested child routes | Single-page implementations |
| **Feature Flags** | `meta.featureFlag` | Not implemented |
| **Permissions** | `meta.permissions` array | Load function checks |
| **Installation Types** | `meta.installationTypes` | Not implemented |
| **Query Parameters** | `route.query` | `$page.url.searchParams` |
| **Route Props** | `props` function | Page data from load |

## Coverage Summary

- **Total Vue Routes**: ~120 routes across all areas
- **Total Svelte Routes**: ~60 routes implemented
- **Coverage Percentage**: ~50% functional coverage
- **Complete Parity**: ~15 routes (Companies, Billing, etc.)
- **Basic Implementation**: ~25 routes (simplified versions)
- **Missing Features**: ~80 routes not implemented
- **Architectural Differences**: Significant (file-based vs explicit routing)

The Svelte implementation focuses on core functionality with simplified UX patterns, while Vue provides comprehensive feature coverage with complex multi-step workflows.

