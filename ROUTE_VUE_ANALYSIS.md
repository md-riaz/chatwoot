# Vue Routes & Component Layout Analysis (app/javascript)

This file lists the main Vue route groups under `app/javascript` (dashboard + widget) and the important layout/inner components used by those routes, with short descriptions of each component's behavior.

## Top-level route groups

| Route Path / Scope | Router name (example) | Primary layout component | Notes / child groups |
|---|---:|---|---|
| `/app/accounts/:accountId` | (various, e.g. `home`, `inbox_view`) | `dashboard/routes/dashboard/Dashboard.vue` (AppContainer) | Aggregates child route groups: captain, inbox, conversation, settings, contacts, companies, search, notifications, helpcenter, campaigns. Acts as the main SPA shell.
| `/app/no-accounts` | `no_accounts` | `dashboard/routes/dashboard/noAccounts/Index.vue` | Shown when user has no accounts.
| `/app/accounts/:accountId/suspended` | `account_suspended` | `dashboard/routes/dashboard/suspended/Index.vue` | Account suspended page.
| Widget SPA (hash) `/` + `/messages`, `/prechat-form`, `/article`, `/unread-messages`, `/campaigns` | `home`, `messages`, `prechat-form`, `article-viewer`, `unread-messages`, `campaigns` | `app/javascript/widget/components/layouts/ViewWithHeader.vue` | Small self-contained widget routes for customer-facing widget.

## Key layout & shell components (dashboard)

| Component file | Role / behavior (short) |
|---|---|
| `dashboard/routes/dashboard/Dashboard.vue` | Main dashboard shell. Renders `NextSidebar`, primary `router-view`, global widgets (CommandBar, CopilotLauncher, MobileSidebarLauncher, CopilotContainer, FloatingCallWidget) and modals (`AddAccountModal`, `WootKeyShortcutModal`, `UpgradePage`). Handles mobile sidebar state and UI settings.
| `dashboard/components-next/sidebar/Sidebar.vue` | Primary application sidebar. Builds sidebar menu (`menuItems`) including Inbox, Conversations, Captain, Contacts, Reports, Campaigns, Portals, Settings. Loads inboxes/labels/teams/custom views, provides keyboard shortcuts, and emits events for modals and mobile sidebar.
| `dashboard/components-next/sidebar/SidebarAccountSwitcher.vue` | Account switcher dropdown inside the sidebar. Shows current account, lists user's accounts, navigates to selected account dashboard, optionally shows "Create new account" action.
| `dashboard/components-next/sidebar/SidebarGroup.vue` | (used by `Sidebar.vue`) Renders a grouped section in the sidebar (label, children, nested items). Handles expansion/collapse and active state.
| `dashboard/components-next/sidebar/SidebarProfileMenu.vue` | Top/bottom profile menu in sidebar. Provides quick actions like keyboard shortcuts, profile access, sign-out links.
| `dashboard/components-next/sidebar/SidebarChangelogCard.vue` | Small banner/card showing changelog or release notes for cloud instances.
| `dashboard/components-next/sidebar/ChannelLeaf.vue` | Compact renderer used for inbox items. Shows inbox label/status inside a sidebar leaf item.
| `dashboard/components-next/NewConversation/ComposeConversation.vue` | New conversation composer modal/trigger embedded in sidebar. Emits new conversation events.
| `dashboard/components/widgets/conversation/ConversationSidebar.vue` | Conversation-specific sidebar used inside conversation views. Shows participant info, contact attributes, timeline and actions relevant to the open conversation.
| `dashboard/components-next/commands/commandbar.vue` | Global command bar (async-loaded). Provides quick navigation and keyboard command palette.
| `dashboard/components-next/sidebar/MobileSidebarLauncher.vue` | Mobile sidebar toggle/launcher used in the shell for small screens.
| `dashboard/components/widgets/FloatingCallWidget.vue` | Small floating widget shown during active/incoming calls.
| `dashboard/components/app/AddAccountModal.vue` | Modal to create or add an account from the dashboard.
| `dashboard/components/widgets/modal/WootKeyShortcutModal.vue` | Modal that displays keyboard shortcuts and quick helpers.
| `dashboard/routes/dashboard/upgrade/UpgradePage.vue` | Wrapper that conditionally shows an upgrade overlay/page; shell routes render inside it when present.

## Representative route modules (child groups)

These files aggregate many routes; each exports `routes` arrays that are mounted as children of the AppContainer.

- `dashboard/routes/dashboard/inbox/routes.js` — Inbox list and inbox-specific dashboards (inbox view, inbox conversations).
- `dashboard/routes/dashboard/conversation/conversation.routes.js` — Conversation-centric routes (conversation list, individual conversation view). Uses `ConversationSidebar`.
- `dashboard/routes/dashboard/settings/*` — Many settings sub-routes (agents, teams, inboxes, labels, automation, billing, security, sla, integratons, etc.). Layouts typically render settings nav + settings pages.
- `dashboard/routes/dashboard/helpcenter/helpcenter.routes.js` — Knowledge base / portal routes; rendered under Portals/Help Center.
- `dashboard/routes/dashboard/campaigns/campaigns.routes.js` — Campaigns routes (livechat, SMS, WhatsApp) used under Campaigns menu.
- `dashboard/routes/dashboard/captain/captain.routes.js` — Captain (AI assistant) routes grouped under Captain menu.
- `dashboard/modules/search/search.routes.js` — Global search route (`search`).

## Widget route components (customer widget)

| Component file | Role / behavior |
|---|---|
| `app/javascript/widget/components/layouts/ViewWithHeader.vue` | Widget top-level layout with header; child views render inside.
| `app/javascript/widget/views/Home.vue` | Widget landing / chat start.
| `app/javascript/widget/views/PreChatForm.vue` | Pre-chat form capture view.
| `app/javascript/widget/views/Messages.vue` | Chat messages view.
| `app/javascript/widget/views/ArticleViewer.vue` | Small article/help viewer inside the widget.

## How this analysis was created

- Examined `dashboard/routes/dashboard/dashboard.routes.js` which mounts `Dashboard.vue` as the main shell for `/app/accounts/:accountId`.
- Inspected `Dashboard.vue` and `dashboard/components-next/sidebar/Sidebar.vue` and `SidebarAccountSwitcher.vue` to enumerate the sidebar and account switcher behavior and other shell components.
- Reviewed `app/javascript/widget/router.js` for the widget SPA routes.

## Next steps / suggestions

- If you want a full, per-route table (every route name → exact component file), I can expand this by parsing each `routes.js` module under `dashboard/routes/dashboard/` and listing every route entry and component path (it will produce a longer file).
- I can also generate a visual map (graph) of route groups and main components if helpful.

---
Generated: analysis of dashboard + widget Vue routes and core layout components.

## Full per-route table (route name → component file)

Below is a comprehensive mapping of route `name` values to the exact Vue component file used by that route (workspace-relative paths). This covers routes mounted under `dashboard/routes/dashboard/` and the widget router.

| Route name | Route path (pattern) | Component file |
|---|---|---|
| accounts (shell) | `/app/accounts/:accountId` | app/javascript/dashboard/routes/dashboard/Dashboard.vue
| account_suspended | `/app/accounts/:accountId/suspended` | app/javascript/dashboard/routes/dashboard/suspended/Index.vue
| no_accounts | `/app/no-accounts` | app/javascript/dashboard/routes/dashboard/noAccounts/Index.vue

<!-- Conversation routes (single view component) -->
| home | `/app/accounts/:accountId/dashboard` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| inbox_conversation | `/app/accounts/:accountId/conversations/:conversation_id` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| inbox_dashboard | `/app/accounts/:accountId/inbox/:inbox_id` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversation_through_inbox | `/app/accounts/:accountId/inbox/:inbox_id/conversations/:conversation_id` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| label_conversations | `/app/accounts/:accountId/label/:label` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversations_through_label | `/app/accounts/:accountId/label/:label/conversations/:conversation_id` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| team_conversations | `/app/accounts/:accountId/team/:teamId` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversations_through_team | `/app/accounts/:accountId/team/:teamId/conversations/:conversationId` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| folder_conversations | `/app/accounts/:accountId/custom_view/:id` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversations_through_folders | `/app/accounts/:accountId/custom_view/:id/conversations/:conversation_id` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversation_mentions | `/app/accounts/:accountId/mentions/conversations` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversation_through_mentions | `/app/accounts/:accountId/mentions/conversations/:conversationId` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversation_unattended | `/app/accounts/:accountId/unattended/conversations` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversation_through_unattended | `/app/accounts/:accountId/unattended/conversations/:conversationId` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversation_participating | `/app/accounts/:accountId/participating/conversations` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue
| conversation_through_participating | `/app/accounts/:accountId/participating/conversations/:conversationId` | app/javascript/dashboard/routes/dashboard/conversation/ConversationView.vue

<!-- Inbox area (dashboard inbox list/details) -->
| inbox_view | `/app/accounts/:accountId/inbox-view` | app/javascript/dashboard/routes/dashboard/inbox/InboxEmptyState.vue
| inbox_view_conversation | `/app/accounts/:accountId/inbox-view/:type/:id` | app/javascript/dashboard/routes/dashboard/inbox/InboxView.vue

<!-- Contacts -->
| contacts_dashboard_index | `/app/accounts/:accountId/contacts` | app/javascript/dashboard/routes/dashboard/contacts/pages/ContactsIndex.vue
| contacts_dashboard_segments_index | `/app/accounts/:accountId/contacts/segments/:segmentId` | app/javascript/dashboard/routes/dashboard/contacts/pages/ContactsIndex.vue
| contacts_dashboard_labels_index | `/app/accounts/:accountId/contacts/labels/:label` | app/javascript/dashboard/routes/dashboard/contacts/pages/ContactsIndex.vue
| contacts_dashboard_active | `/app/accounts/:accountId/contacts/active` | app/javascript/dashboard/routes/dashboard/contacts/pages/ContactsIndex.vue
| contacts_edit | `/app/accounts/:accountId/contacts/:contactId` | app/javascript/dashboard/routes/dashboard/contacts/pages/ContactManageView.vue
| contacts_edit_segment | `/app/accounts/:accountId/contacts/:contactId/segments/:segmentId` | app/javascript/dashboard/routes/dashboard/contacts/pages/ContactManageView.vue
| contacts_edit_label | `/app/accounts/:accountId/contacts/:contactId/labels/:label` | app/javascript/dashboard/routes/dashboard/contacts/pages/ContactManageView.vue

<!-- Companies -->
| companies_dashboard_index | `/app/accounts/:accountId/companies` | app/javascript/dashboard/routes/dashboard/companies/pages/CompaniesIndex.vue

<!-- Notifications -->
| notifications_index | `/app/accounts/:accountId/notifications` | app/javascript/dashboard/routes/dashboard/notifications/components/NotificationsView.vue

<!-- Search -->
| search | `/app/accounts/:accountId/search/:tab?` | app/javascript/dashboard/modules/search/components/SearchView.vue

<!-- Help Center / Portals -->
| portals_articles_index | portal route (see helper) | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsArticlesIndexPage.vue
| portals_articles_new | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsArticlesNewPage.vue
| portals_articles_edit | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsArticlesEditPage.vue
| portals_categories_index | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsCategoriesIndexPage.vue
| portals_categories_articles_index | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsArticlesIndexPage.vue
| portals_categories_articles_edit | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsArticlesEditPage.vue
| portals_locales_index | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsLocalesIndexPage.vue
| portals_settings_index | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsSettingsIndexPage.vue
| portals_new | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsNewPage.vue
| portals_index | portal route | app/javascript/dashboard/routes/dashboard/helpcenter/pages/PortalsIndexPage.vue

<!-- Campaigns -->
| campaigns_ongoing_index | `/app/accounts/:accountId/campaigns/ongoing` | app/javascript/dashboard/routes/dashboard/campaigns/pages/LiveChatCampaignsPage.vue (redirects to livechat)
| campaigns_one_off_index | `/app/accounts/:accountId/campaigns/one_off` | app/javascript/dashboard/routes/dashboard/campaigns/pages/SMSCampaignsPage.vue (redirects to sms)
| campaigns_livechat_index | `/app/accounts/:accountId/campaigns/live_chat` | app/javascript/dashboard/routes/dashboard/campaigns/pages/LiveChatCampaignsPage.vue
| campaigns_sms_index | `/app/accounts/:accountId/campaigns/sms` | app/javascript/dashboard/routes/dashboard/campaigns/pages/SMSCampaignsPage.vue
| campaigns_whatsapp_index | `/app/accounts/:accountId/campaigns/whatsapp` | app/javascript/dashboard/routes/dashboard/campaigns/pages/WhatsAppCampaignsPage.vue

<!-- Captain (dashboard) -->
| captain_assistants_responses_index | `/app/accounts/:accountId/captain/:assistantId/faqs` | app/javascript/dashboard/routes/dashboard/captain/responses/Index.vue (ResponsesIndex)
| captain_assistants_documents_index | `/app/accounts/:accountId/captain/:assistantId/documents` | app/javascript/dashboard/routes/dashboard/captain/documents/Index.vue (DocumentsIndex)
| captain_tools_index | `/app/accounts/:accountId/captain/:assistantId/tools` | app/javascript/dashboard/routes/dashboard/captain/tools/Index.vue (CustomToolsIndex)
| captain_assistants_scenarios_index | `/app/accounts/:accountId/captain/:assistantId/scenarios` | app/javascript/dashboard/routes/dashboard/captain/assistants/scenarios/Index.vue
| captain_assistants_playground_index | `/app/accounts/:accountId/captain/:assistantId/playground` | app/javascript/dashboard/routes/dashboard/captain/assistants/playground/Index.vue
| captain_assistants_inboxes_index | `/app/accounts/:accountId/captain/:assistantId/inboxes` | app/javascript/dashboard/routes/dashboard/captain/assistants/inboxes/Index.vue
| captain_assistants_responses_pending | `/app/accounts/:accountId/captain/:assistantId/faqs/pending` | app/javascript/dashboard/routes/dashboard/captain/responses/Pending.vue
| captain_assistants_settings_index | `/app/accounts/:accountId/captain/:assistantId/settings` | app/javascript/dashboard/routes/dashboard/captain/assistants/settings/Settings.vue
| captain_assistants_guardrails_index | `/app/accounts/:accountId/captain/:assistantId/settings/guardrails` | app/javascript/dashboard/routes/dashboard/captain/assistants/guardrails/Index.vue
| captain_assistants_guidelines_index | `/app/accounts/:accountId/captain/:assistantId/settings/guidelines` | app/javascript/dashboard/routes/dashboard/captain/assistants/guidelines/Index.vue
| captain_assistants_create_index | `/app/accounts/:accountId/captain/assistants` | app/javascript/dashboard/routes/dashboard/captain/assistants/Index.vue (AssistantEmptyStateIndex)
| captain_assistants_index | `/app/accounts/:accountId/captain/:navigationPath` | app/javascript/dashboard/routes/dashboard/captain/pages/AssistantsIndexPage.vue

<!-- Settings (per-submodule routes) -->
| settings_home | `/app/accounts/:accountId/settings` | (redirect) — delegates to child settings routes (see below)

-- Account settings
| general_settings_index | `/app/accounts/:accountId/settings/general` | app/javascript/dashboard/routes/dashboard/settings/account/Index.vue

-- Agents
| agent_list | `/app/accounts/:accountId/settings/agents/list` | app/javascript/dashboard/routes/dashboard/settings/agents/Index.vue

-- Agent bots
| agent_bots | `/app/accounts/:accountId/settings/agent-bots` | app/javascript/dashboard/routes/dashboard/settings/agentBots/Index.vue

-- Assignment policy
| assignment_policy_index | `/app/accounts/:accountId/settings/assignment-policy/index` | app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/Index.vue
| agent_assignment_policy_index | `/app/accounts/:accountId/settings/assignment-policy/assignment` | app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/pages/AgentAssignmentIndexPage.vue
| agent_assignment_policy_create | `/app/accounts/:accountId/settings/assignment-policy/assignment/create` | app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/pages/AgentAssignmentCreatePage.vue
| agent_assignment_policy_edit | `/app/accounts/:accountId/settings/assignment-policy/assignment/edit/:id` | app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/pages/AgentAssignmentEditPage.vue
| agent_capacity_policy_index | `/app/accounts/:accountId/settings/assignment-policy/capacity` | app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/pages/AgentCapacityIndexPage.vue
| agent_capacity_policy_create | `/app/accounts/:accountId/settings/assignment-policy/capacity/create` | app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/pages/AgentCapacityCreatePage.vue
| agent_capacity_policy_edit | `/app/accounts/:accountId/settings/assignment-policy/capacity/edit/:id` | app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/pages/AgentCapacityEditPage.vue

-- Attributes
| attributes_list | `/app/accounts/:accountId/settings/custom-attributes/list` | app/javascript/dashboard/routes/dashboard/settings/attributes/Index.vue

-- Automation
| automation_list | `/app/accounts/:accountId/settings/automation/list` | app/javascript/dashboard/routes/dashboard/settings/automation/Index.vue

-- Audit logs
| auditlogs_list | `/app/accounts/:accountId/settings/audit-logs/list` | app/javascript/dashboard/routes/dashboard/settings/auditlogs/Index.vue

-- Billing
| billing_settings_index | `/app/accounts/:accountId/settings/billing` | app/javascript/dashboard/routes/dashboard/settings/billing/Index.vue

-- Canned responses
| canned_list | `/app/accounts/:accountId/settings/canned-response/list` | app/javascript/dashboard/routes/dashboard/settings/canned/Index.vue

-- Integrations
| settings_applications | `/app/accounts/:accountId/settings/integrations` | app/javascript/dashboard/routes/dashboard/settings/integrations/Index.vue
| settings_integrations_dashboard_apps | `/app/accounts/:accountId/settings/integrations/dashboard_apps` | app/javascript/dashboard/routes/dashboard/settings/integrations/DashboardApps/Index.vue
| settings_integrations_webhook | `/app/accounts/:accountId/settings/integrations/webhook` | app/javascript/dashboard/routes/dashboard/settings/integrations/Webhooks/Index.vue
| settings_integrations_slack | `/app/accounts/:accountId/settings/integrations/slack` | app/javascript/dashboard/routes/dashboard/settings/integrations/Slack.vue
| settings_integrations_linear | `/app/accounts/:accountId/settings/integrations/linear` | app/javascript/dashboard/routes/dashboard/settings/integrations/Linear.vue
| settings_integrations_notion | `/app/accounts/:accountId/settings/integrations/notion` | app/javascript/dashboard/routes/dashboard/settings/integrations/Notion.vue
| settings_integrations_shopify | `/app/accounts/:accountId/settings/integrations/shopify` | app/javascript/dashboard/routes/dashboard/settings/integrations/Shopify.vue
| settings_applications_integration | `/app/accounts/:accountId/settings/integrations/:integration_id` | app/javascript/dashboard/routes/dashboard/settings/integrations/IntegrationHooks.vue

-- Labels
| labels_list | `/app/accounts/:accountId/settings/labels/list` | app/javascript/dashboard/routes/dashboard/settings/labels/Index.vue

-- Macros
| macros_wrapper | `/app/accounts/:accountId/settings/macros` | app/javascript/dashboard/routes/dashboard/settings/macros/Index.vue
| macros_edit | `/app/accounts/:accountId/settings/macros/:macroId/edit` | app/javascript/dashboard/routes/dashboard/settings/macros/MacroEditor.vue
| macros_new | `/app/accounts/:accountId/settings/macros/new` | app/javascript/dashboard/routes/dashboard/settings/macros/MacroEditor.vue

-- Profile
| profile_settings | `/app/accounts/:accountId/profile` | app/javascript/dashboard/routes/dashboard/settings/profile/Wrapper.vue
| profile_settings_index | `/app/accounts/:accountId/profile/settings` | app/javascript/dashboard/routes/dashboard/settings/profile/Index.vue
| profile_settings_mfa | `/app/accounts/:accountId/profile/mfa` | app/javascript/dashboard/routes/dashboard/settings/profile/MfaSettings.vue

-- Reports (many)
| account_overview_reports | `/app/accounts/:accountId/reports/overview` | app/javascript/dashboard/routes/dashboard/settings/reports/LiveReports.vue
| conversation_reports | `/app/accounts/:accountId/reports/conversation` | app/javascript/dashboard/routes/dashboard/settings/reports/Index.vue
| agent_reports | `/app/accounts/:accountId/reports/agent` | app/javascript/dashboard/routes/dashboard/settings/reports/AgentReports.vue
| inbox_reports | `/app/accounts/:accountId/reports/inboxes` | app/javascript/dashboard/routes/dashboard/settings/reports/InboxReports.vue
| label_reports | `/app/accounts/:accountId/reports/label` | app/javascript/dashboard/routes/dashboard/settings/reports/LabelReports.vue
| team_reports | `/app/accounts/:accountId/reports/teams` | app/javascript/dashboard/routes/dashboard/settings/reports/TeamReports.vue
| agent_reports_index | `/app/accounts/:accountId/reports/agents_overview` | app/javascript/dashboard/routes/dashboard/settings/reports/AgentReportsIndex.vue
| agent_reports_show | `/app/accounts/:accountId/reports/agents/:id` | app/javascript/dashboard/routes/dashboard/settings/reports/AgentReportsShow.vue
| inbox_reports_index | `/app/accounts/:accountId/reports/inboxes_overview` | app/javascript/dashboard/routes/dashboard/settings/reports/InboxReportsIndex.vue
| inbox_reports_show | `/app/accounts/:accountId/reports/inboxes/:id` | app/javascript/dashboard/routes/dashboard/settings/reports/InboxReportsShow.vue
| team_reports_index | `/app/accounts/:accountId/reports/teams_overview` | app/javascript/dashboard/routes/dashboard/settings/reports/TeamReportsIndex.vue
| team_reports_show | `/app/accounts/:accountId/reports/teams/:id` | app/javascript/dashboard/routes/dashboard/settings/reports/TeamReportsShow.vue
| label_reports_index | `/app/accounts/:accountId/reports/labels_overview` | app/javascript/dashboard/routes/dashboard/settings/reports/LabelReportsIndex.vue
| label_reports_show | `/app/accounts/:accountId/reports/labels/:id` | app/javascript/dashboard/routes/dashboard/settings/reports/LabelReportsShow.vue
| sla_reports | `/app/accounts/:accountId/reports/sla` | app/javascript/dashboard/routes/dashboard/settings/reports/SLAReports.vue
| csat_reports | `/app/accounts/:accountId/reports/csat` | app/javascript/dashboard/routes/dashboard/settings/reports/CsatResponses.vue
| bot_reports | `/app/accounts/:accountId/reports/bot` | app/javascript/dashboard/routes/dashboard/settings/reports/BotReports.vue

-- SLA
| sla_wrapper | `/app/accounts/:accountId/settings/sla` | app/javascript/dashboard/routes/dashboard/settings/sla/Index.vue
| sla_list | `/app/accounts/:accountId/settings/sla/list` | app/javascript/dashboard/routes/dashboard/settings/sla/Index.vue

-- Custom roles
| custom_roles_list | `/app/accounts/:accountId/settings/custom-roles/list` | app/javascript/dashboard/routes/dashboard/settings/customRoles/Index.vue

<!-- Widget routes (app/javascript/widget/router.js) -->
| home (widget) | `/#/` (widget root) | app/javascript/widget/views/Home.vue
| messages (widget) | `/#/messages` | app/javascript/widget/views/Messages.vue
| prechat-form (widget) | `/#/prechat-form` | app/javascript/widget/views/PreChatForm.vue
| article-viewer (widget) | `/#/article` | app/javascript/widget/views/ArticleViewer.vue
| unread-messages (widget) | `/#/unread-messages` | app/javascript/widget/views/UnreadMessages.vue
| campaigns (widget) | `/#/campaigns` | app/javascript/widget/views/Campaigns.vue

> Note: Some display-only parent routes use wrapper components (e.g., `SettingsWrapper.vue`, `SettingsContent.vue`) and children map to the concrete page components listed above. Dynamic imports (lazy-loaded components) are shown with their resolved paths above.
