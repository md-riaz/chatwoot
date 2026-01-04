# Vue Frontend vs Svelte-UI SPA Route URL Comparison

This document inventories every Vue frontend route (dashboard, V3 auth shell, and widget) and compares them with the current Svelte-UI SPA routes. Paths are shown with their dynamic segments (`:accountId`, `:id`, etc.) exactly as defined so no route is omitted.

## Source files
- Vue dashboard routes: `app/javascript/dashboard/routes/**/*.routes.js` (loaded via `routes/index.js`).【F:app/javascript/dashboard/routes/index.js†L1-L45】
- Vue V3 auth shell: `app/javascript/v3/views/routes.js`.【F:app/javascript/v3/views/routes.js†L1-L65】
- Vue widget shell: `app/javascript/widget/router.js`.【F:app/javascript/widget/router.js†L1-L69】
- Svelte SPA routes: `custom/ui/svelte-ui/src/routes/**/+page.*`.【F:custom/ui/svelte-ui/src/routes/app/accounts/[accountId]/conversations/[id]/+page.svelte†L1-L17】

## Vue → Svelte route coverage

| Area | Vue route URLs | Svelte SPA route URLs | Notes |
| --- | --- | --- | --- |
| Auth (V3 shell) | `/app/login`, `/app/login/sso`, `/app/auth/signup`, `/app/auth/confirmation`, `/app/auth/password/edit`, `/app/auth/reset/password`【F:app/javascript/v3/views/routes.js†L9-L65】 | `/login`, `/auth/login`, `/app/login`, `/auth/register`, `/app/auth/signup` | Vue covers more recovery/confirmation flows; Svelte currently surfaces login/signup without the password reset/confirmation paths. |
| Account shell & guards | `/app/accounts/:accountId` (dashboard container), `/app/accounts/:accountId/suspended`, `/app/no-accounts`【F:app/javascript/dashboard/routes/dashboard/dashboard.routes.js†L17-L45】 | `/app/accounts/:accountId` | Svelte lacks explicit suspended/no-account routes (uses generic `/app/unauthorized`). |
| Conversations | `/app/accounts/:accountId/dashboard`, `/app/accounts/:accountId/conversations/:conversation_id`, `/app/accounts/:accountId/inbox/:inbox_id`, `/app/accounts/:accountId/inbox/:inbox_id/conversations/:conversation_id`, `/app/accounts/:accountId/label/:label`, `/app/accounts/:accountId/label/:label/conversations/:conversation_id`, `/app/accounts/:accountId/team/:teamId`, `/app/accounts/:accountId/team/:teamId/conversations/:conversationId`, `/app/accounts/:accountId/custom_view/:id`, `/app/accounts/:accountId/custom_view/:id/conversations/:conversation_id`, `/app/accounts/:accountId/mentions/conversations`, `/app/accounts/:accountId/mentions/conversations/:conversationId`, `/app/accounts/:accountId/unattended/conversations`, `/app/accounts/:accountId/unattended/conversations/:conversationId`, `/app/accounts/:accountId/participating/conversations`, `/app/accounts/:accountId/participating/conversations/:conversationId`【F:app/javascript/dashboard/routes/dashboard/conversation/conversation.routes.js†L6-L205】 | `/app/accounts/:accountId/conversations`, `/app/accounts/:accountId/conversations/:id` | Vue supports inbox/label/team/custom-view/mention/participating filters; Svelte currently exposes list/detail only. |
| Inbox view | `/app/accounts/:accountId/inbox-view`, `/app/accounts/:accountId/inbox-view/:type/:id`【F:app/javascript/dashboard/routes/dashboard/inbox/routes.js†L8-L34】 | — | Svelte has no dedicated inbox-view shell yet. |
| Contacts | `/app/accounts/:accountId/contacts`, `/app/accounts/:accountId/contacts/segments/:segmentId`, `/app/accounts/:accountId/contacts/labels/:label`, `/app/accounts/:accountId/contacts/active`, `/app/accounts/:accountId/contacts/:contactId`, `/app/accounts/:accountId/contacts/:contactId/segments/:segmentId`, `/app/accounts/:accountId/contacts/:contactId/labels/:label`【F:app/javascript/dashboard/routes/dashboard/contacts/routes.js†L11-L73】 | `/app/accounts/:accountId/contacts` | Svelte has a single contacts surface; Vue includes segment/label filters and edit paths. |
| Companies | `/app/accounts/:accountId/companies`【F:app/javascript/dashboard/routes/dashboard/companies/routes.js†L12-L28】 | `/app/accounts/:accountId/companies` | Parity on entry point; Vue uses child route for index. |
| Campaigns | `/app/accounts/:accountId/campaigns` (redirect), `/app/accounts/:accountId/campaigns/ongoing`, `/app/accounts/:accountId/campaigns/one_off`, `/app/accounts/:accountId/campaigns/live_chat`, `/app/accounts/:accountId/campaigns/sms`, `/app/accounts/:accountId/campaigns/whatsapp`【F:app/javascript/dashboard/routes/dashboard/campaigns/campaigns.routes.js†L9-L69】 | `/app/accounts/:accountId/campaigns` | Vue splits per campaign type; Svelte uses a single page. |
| Captain (AI workspace) | `/app/accounts/:accountId/captain`, `/app/accounts/:accountId/captain/:navigationPath`, `/app/accounts/:accountId/captain/assistants`, `/app/accounts/:accountId/captain/:assistantId/faqs`, `/app/accounts/:accountId/captain/:assistantId/faqs/pending`, `/app/accounts/:accountId/captain/:assistantId/documents`, `/app/accounts/:accountId/captain/:assistantId/tools`, `/app/accounts/:accountId/captain/:assistantId/scenarios`, `/app/accounts/:accountId/captain/:assistantId/playground`, `/app/accounts/:accountId/captain/:assistantId/inboxes`, `/app/accounts/:accountId/captain/:assistantId/settings`, `/app/accounts/:accountId/captain/:assistantId/settings/guardrails`, `/app/accounts/:accountId/captain/:assistantId/settings/guidelines`【F:app/javascript/dashboard/routes/dashboard/captain/captain.routes.js†L29-L144】 | — | Captain routes are not yet represented in Svelte-UI. |
| Notifications | `/app/accounts/:accountId/notifications`【F:app/javascript/dashboard/routes/dashboard/notifications/routes.js†L6-L30】 | `/app/accounts/:accountId/settings/notifications` | Svelte places notifications under settings instead of a top-level dashboard path. |
| Search | `/app/accounts/:accountId/search/:tab?`【F:app/javascript/dashboard/modules/search/search.routes.js†L13-L31】 | — | No Svelte search route yet. |
| Help Center (portals) | `/app/accounts/:accountId/portals` (container) plus `/app/accounts/:accountId/portals/:portalSlug/:locale/:categorySlug?/articles/:tab?`, `/app/accounts/:accountId/portals/:portalSlug/:locale/:categorySlug?/articles/new`, `/app/accounts/:accountId/portals/:portalSlug/:locale/:categorySlug?/articles/:tab?/edit/:articleSlug`, `/app/accounts/:accountId/portals/:portalSlug/:locale/categories`, `/app/accounts/:accountId/portals/:portalSlug/:locale/categories/:categorySlug/articles`, `/app/accounts/:accountId/portals/:portalSlug/:locale/categories/:categorySlug/articles/:articleSlug`, `/app/accounts/:accountId/portals/:portalSlug/locales`, `/app/accounts/:accountId/portals/:portalSlug/settings`, `/app/accounts/:accountId/portals/new`, `/app/accounts/:accountId/portals/:navigationPath`【F:app/javascript/dashboard/routes/dashboard/helpcenter/helpcenter.routes.js†L15-L111】 | `/portal`, `/portal/articles/:slug`, `/portal/categories/:slug` | Svelte portal pages are public-scoped, not account-scoped. |
| Reports (dashboard nav) | `/app/accounts/:accountId/reports` (redirect), `/app/accounts/:accountId/reports/overview`, `/app/accounts/:accountId/reports/conversation`, `/app/accounts/:accountId/reports/agent`, `/app/accounts/:accountId/reports/inboxes`, `/app/accounts/:accountId/reports/label`, `/app/accounts/:accountId/reports/teams`, `/app/accounts/:accountId/reports/agents_overview`, `/app/accounts/:accountId/reports/agents/:id`, `/app/accounts/:accountId/reports/inboxes_overview`, `/app/accounts/:accountId/reports/inboxes/:id`, `/app/accounts/:accountId/reports/teams_overview`, `/app/accounts/:accountId/reports/teams/:id`, `/app/accounts/:accountId/reports/labels_overview`, `/app/accounts/:accountId/reports/labels/:id`, `/app/accounts/:accountId/reports/sla`, `/app/accounts/:accountId/reports/csat`, `/app/accounts/:accountId/reports/bot`【F:app/javascript/dashboard/routes/dashboard/settings/reports/reports.routes.js†L69-L177】 | `/app/accounts/:accountId/reports` | Vue offers numerous sub-reports; Svelte has a single reports surface today. |
| Settings – landing | `/app/accounts/:accountId/settings` (redirect to role-specific target)【F:app/javascript/dashboard/routes/dashboard/settings/settings.routes.js†L27-L56】 | `/app/accounts/:accountId/settings` | Both provide a settings entry point. |
| Settings – general | `/app/accounts/:accountId/settings/general`【F:app/javascript/dashboard/routes/dashboard/settings/account/account.routes.js†L6-L26】 | `/app/accounts/:accountId/settings/account` | Parity on general settings entry. |
| Settings – agents | `/app/accounts/:accountId/settings/agents`, `/app/accounts/:accountId/settings/agents/list`【F:app/javascript/dashboard/routes/dashboard/settings/agents/agent.routes.js†L7-L31】 | `/app/accounts/:accountId/settings/agents` | Vue splits list view; Svelte single page. |
| Settings – assignment policy | `/app/accounts/:accountId/settings/assignment-policy`, `/app/accounts/:accountId/settings/assignment-policy/index`, `/app/accounts/:accountId/settings/assignment-policy/assignment`, `/app/accounts/:accountId/settings/assignment-policy/assignment/create`, `/app/accounts/:accountId/settings/assignment-policy/assignment/edit/:id`, `/app/accounts/:accountId/settings/assignment-policy/capacity`, `/app/accounts/:accountId/settings/assignment-policy/capacity/create`, `/app/accounts/:accountId/settings/assignment-policy/capacity/edit/:id`【F:app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/assignmentPolicy.routes.js†L11-L92】 | — | Assignment policy pages are missing in Svelte. |
| Settings – agent bots | `/app/accounts/:accountId/settings/agent-bots`【F:app/javascript/dashboard/routes/dashboard/settings/agentBots/agentBot.routes.js†L7-L29】 | `/app/super_admin/agent-bots`, `/app/super_admin/agent-bots/:id`, `/app/super_admin/agent-bots/new` | Svelte implements agent bots in the super-admin area rather than per-account settings. |
| Settings – custom attributes | `/app/accounts/:accountId/settings/custom-attributes`, `/app/accounts/:accountId/settings/custom-attributes/list`【F:app/javascript/dashboard/routes/dashboard/settings/attributes/attributes.routes.js†L7-L31】 | `/app/accounts/:accountId/settings/attributes` | Svelte combines the list into one page. |
| Settings – automation | `/app/accounts/:accountId/settings/automation`, `/app/accounts/:accountId/settings/automation/list`【F:app/javascript/dashboard/routes/dashboard/settings/automation/automation.routes.js†L7-L31】 | `/app/accounts/:accountId/settings/automation` | Single-page in Svelte. |
| Settings – audit logs | `/app/accounts/:accountId/settings/audit-logs`, `/app/accounts/:accountId/settings/audit-logs/list`【F:app/javascript/dashboard/routes/dashboard/settings/auditlogs/audit.routes.js†L7-L38】 | `/app/accounts/:accountId/settings/audit-logs` | Single-page in Svelte. |
| Settings – billing | `/app/accounts/:accountId/settings/billing`【F:app/javascript/dashboard/routes/dashboard/settings/billing/billing.routes.js†L7-L37】 | `/app/accounts/:accountId/settings/billing` | Parity on entry point. |
| Settings – canned responses | `/app/accounts/:accountId/settings/canned-response`, `/app/accounts/:accountId/settings/canned-response/list`【F:app/javascript/dashboard/routes/dashboard/settings/canned/canned.routes.js†L11-L34】 | `/app/accounts/:accountId/canned-responses` | Svelte keeps canned responses outside settings. |
| Settings – inboxes | `/app/accounts/:accountId/settings/inboxes`, `/list`, `/new`, `/new/:inbox_id/finish`, `/new/:sub_page`, `/new/:inbox_id/agents`, `/app/accounts/:accountId/settings/inboxes/:inboxId/:tab?`【F:app/javascript/dashboard/routes/dashboard/settings/inbox/inbox.routes.js†L11-L110】 | `/app/accounts/:accountId/settings/inboxes`, `/app/accounts/:accountId/settings/inboxes/new`, `/app/accounts/:accountId/settings/inboxes/:id` | Svelte covers list/new/detail; Vue adds wizard sub-pages for channel types and agent steps. |
| Settings – integrations | `/app/accounts/:accountId/settings/integrations`, `/dashboard_apps`, `/webhook`, `/slack`, `/linear`, `/notion`, `/shopify`, `/app/accounts/:accountId/settings/integrations/:integration_id`【F:app/javascript/dashboard/routes/dashboard/settings/integrations/integrations.routes.js†L15-L122】 | `/app/accounts/:accountId/integrations` | Svelte consolidates integrations into one page. |
| Settings – labels | `/app/accounts/:accountId/settings/labels`, `/app/accounts/:accountId/settings/labels/list`【F:app/javascript/dashboard/routes/dashboard/settings/labels/labels.routes.js†L8-L33】 | `/app/accounts/:accountId/labels` | Svelte exposes labels as a top-level account page. |
| Settings – macros | `/app/accounts/:accountId/settings/macros`, `/app/accounts/:accountId/settings/macros/:macroId/edit`, `/app/accounts/:accountId/settings/macros/new`【F:app/javascript/dashboard/routes/dashboard/settings/macros/macros.routes.js†L8-L53】 | `/app/accounts/:accountId/settings/macros` | Edit/new handled inside single Svelte page. |
| Settings – SLA | `/app/accounts/:accountId/settings/sla`, `/app/accounts/:accountId/settings/sla/list`【F:app/javascript/dashboard/routes/dashboard/settings/sla/sla.routes.js†L13-L41】 | `/app/accounts/:accountId/settings/sla` | Single-page in Svelte. |
| Settings – teams | `/app/accounts/:accountId/settings/teams`, `/list`, `/new`, `/new/:teamId/finish`, `/new/:teamId/agents`, `/app/accounts/:accountId/settings/teams/:teamId/edit`, `/app/accounts/:accountId/settings/teams/:teamId/edit/agents`, `/app/accounts/:accountId/settings/teams/:teamId/edit/finish`【F:app/javascript/dashboard/routes/dashboard/settings/teams/teams.routes.js†L16-L124】 | `/app/accounts/:accountId/team` | Vue has multi-step team creation/edit flows; Svelte exposes a single team page. |
| Settings – custom roles | `/app/accounts/:accountId/settings/custom-roles`, `/app/accounts/:accountId/settings/custom-roles/list`【F:app/javascript/dashboard/routes/dashboard/settings/customRoles/customRole.routes.js†L7-L35】 | — | Custom roles not yet present in Svelte. |
| Settings – profile | `/app/accounts/:accountId/profile/settings`, `/app/accounts/:accountId/profile/mfa`【F:app/javascript/dashboard/routes/dashboard/settings/profile/profile.routes.js†L7-L55】 | `/app/accounts/:accountId/settings/profile` | Svelte combines profile and MFA under one page. |
| Settings – security | `/app/accounts/:accountId/settings/security`【F:app/javascript/dashboard/routes/dashboard/settings/security/security.routes.js†L7-L42】 | — | Security (SAML) page not yet mirrored in Svelte. |
| Notifications (top-level) | `/app/accounts/:accountId/notifications`【F:app/javascript/dashboard/routes/dashboard/notifications/routes.js†L6-L30】 | `/app/accounts/:accountId/settings/notifications` | Kept here for completeness; Vue treats notifications outside settings. |
| Widget shell | `/#/` (home), `/#/prechat-form`, `/#/messages`, `/#/article`, `/#/unread-messages`, `/#/campaigns`【F:app/javascript/widget/router.js†L5-L47】 | `/widget` | Svelte provides a single widget preview route instead of hash-based child routes. |

## Svelte route inventory with Vue counterparts

| Svelte SPA route URL | Vue counterpart(s) | Notes |
| --- | --- | --- |
| `/` | — | Svelte landing page only. |
| `/login` | `/app/login` | Data loader mirrors login experience. |
| `/auth/login` | `/app/login` | Direct auth login page. |
| `/auth/register` | `/app/auth/signup` | Signup flow. |
| `/app/login` | `/app/login` | Alias kept for app shell parity. |
| `/app/auth/signup` | `/app/auth/signup` | Signup path. |
| `/onboarding` | — | Svelte-only onboarding helper. |
| `/unauthorized` | `/app/accounts/:accountId/suspended` | Generic access denial; Vue has specific suspended/no-account pages. |
| `/portal` | `/app/accounts/:accountId/portals/:navigationPath` | Public portal list (not account-scoped). |
| `/portal/articles/:slug` | `/app/accounts/:accountId/portals/.../articles/:tab?` | Article display (public). |
| `/portal/categories/:slug` | `/app/accounts/:accountId/portals/:portalSlug/:locale/categories` | Category display (public). |
| `/ui/:name` | — | Component catalog demo. |
| `/widget` | `/#/`, `/#/messages`, etc. | Unified widget preview for Svelte. |
| `/app` | `/app/accounts/:accountId` | Shell entry; Vue routes immediately require account context. |
| `/app/accounts/:accountId` | `/app/accounts/:accountId` | Account home. |
| `/app/accounts/:accountId/canned-responses` | `/app/accounts/:accountId/settings/canned-response` | Different placement; feature parity pending. |
| `/app/accounts/:accountId/labels` | `/app/accounts/:accountId/settings/labels` | Labels surfaced at top level in Svelte. |
| `/app/accounts/:accountId/team` | `/app/accounts/:accountId/settings/teams` | Single team surface instead of multi-step settings pages. |
| `/app/accounts/:accountId/contacts` | `/app/accounts/:accountId/contacts` | Contacts list. |
| `/app/accounts/:accountId/companies` | `/app/accounts/:accountId/companies` | Companies list. |
| `/app/accounts/:accountId/conversations` | `/app/accounts/:accountId/dashboard` | Conversation list (no filter routes yet). |
| `/app/accounts/:accountId/conversations/:id` | `/app/accounts/:accountId/conversations/:conversation_id` | Conversation detail. |
| `/app/accounts/:accountId/campaigns` | `/app/accounts/:accountId/campaigns/*` | Campaigns entry (no per-type splits). |
| `/app/accounts/:accountId/integrations` | `/app/accounts/:accountId/settings/integrations/*` | Integrations consolidated. |
| `/app/accounts/:accountId/reports` | `/app/accounts/:accountId/reports/*` | Single reports page. |
| `/app/accounts/:accountId/settings` | `/app/accounts/:accountId/settings` | Settings landing. |
| `/app/accounts/:accountId/settings/account` | `/app/accounts/:accountId/settings/general` | General settings. |
| `/app/accounts/:accountId/settings/inboxes` | `/app/accounts/:accountId/settings/inboxes` | Inbox list. |
| `/app/accounts/:accountId/settings/inboxes/new` | `/app/accounts/:accountId/settings/inboxes/new` | Inbox creation (wizard steps condensed). |
| `/app/accounts/:accountId/settings/inboxes/:id` | `/app/accounts/:accountId/settings/inboxes/:inboxId/:tab?` | Inbox detail (no tab param split). |
| `/app/accounts/:accountId/settings/notifications` | `/app/accounts/:accountId/notifications` | Notifications located in settings. |
| `/app/accounts/:accountId/settings/automation` | `/app/accounts/:accountId/settings/automation/list` | Automation list. |
| `/app/accounts/:accountId/settings/sla` | `/app/accounts/:accountId/settings/sla/list` | SLA list. |
| `/app/accounts/:accountId/settings/profile` | `/app/accounts/:accountId/profile/*` | Profile & MFA combined. |
| `/app/accounts/:accountId/settings/audit-logs` | `/app/accounts/:accountId/settings/audit-logs/list` | Audit logs list. |
| `/app/accounts/:accountId/settings/billing` | `/app/accounts/:accountId/settings/billing` | Billing. |
| `/app/accounts/:accountId/settings/macros` | `/app/accounts/:accountId/settings/macros/*` | Macro list/edit combined. |
| `/app/accounts/:accountId/settings/attributes` | `/app/accounts/:accountId/settings/custom-attributes/list` | Custom attributes. |
| `/app/accounts/:accountId/settings/agents` | `/app/accounts/:accountId/settings/agents/list` | Agent management. |
| `/app/super_admin/dashboard` | — (Rails-based super admin in Vue) | Super admin dashboard (Svelte-only SPA). |
| `/app/super_admin/settings` | — | Super admin settings (Svelte-only). |
| `/app/super_admin/platform-apps` | — | Platform apps list. |
| `/app/super_admin/platform-apps/:id` | — | Platform app detail. |
| `/app/super_admin/accounts` | — | Accounts list. |
| `/app/super_admin/accounts/new` | — | Account creation. |
| `/app/super_admin/accounts/:id` | — | Account detail. |
| `/app/super_admin/agent-bots` | `/app/accounts/:accountId/settings/agent-bots` | Agent bots handled globally in Svelte. |
| `/app/super_admin/agent-bots/new` | `/app/accounts/:accountId/settings/agent-bots` | Creation variant. |
| `/app/super_admin/agent-bots/:id` | `/app/accounts/:accountId/settings/agent-bots` | Detail variant. |
| `/app/super_admin/users` | — | Super admin users list. |
| `/app/super_admin/users/new` | — | User creation. |
| `/app/super_admin/users/:id` | — | User detail. |
| `/survey` | — | Public survey entry. |
| `/survey/thank-you` | — | Survey confirmation. |
| `/app/unauthorized` | `/app/accounts/:accountId/suspended`, `/app/no-accounts` | Generic unauthorized page. |

