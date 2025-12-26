# ClearLine Laravel 12 Project - Folder Structure

This document outlines the complete folder structure for the ClearLine Laravel 12 conversion project using best practices, Reverb for WebSocket, and modern Laravel patterns.

## 📁 Complete Folder Hierarchy

```
custom/laravel/
├── app/
│   ├── Actions/                          # Lorisleiva Laravel Actions (Business Logic)
│   │   ├── Account/
│   │   │   ├── CreateAccountAction.php
│   │   │   ├── UpdateAccountAction.php
│   │   │   └── DeleteAccountAction.php
│   │   ├── Conversation/
│   │   │   ├── CreateConversationAction.php
│   │   │   ├── UpdateConversationAction.php
│   │   │   ├── AssignConversationAction.php
│   │   │   ├── CloseConversationAction.php
│   │   │   └── ReopenConversationAction.php
│   │   ├── Message/
│   │   │   ├── CreateMessageAction.php
│   │   │   ├── UpdateMessageAction.php
│   │   │   └── DeleteMessageAction.php
│   │   ├── Contact/
│   │   │   ├── CreateContactAction.php
│   │   │   ├── UpdateContactAction.php
│   │   │   ├── MergeContactsAction.php
│   │   │   └── DeleteContactAction.php
│   │   ├── Inbox/
│   │   │   ├── CreateInboxAction.php
│   │   │   ├── UpdateInboxAction.php
│   │   │   └── DeleteInboxAction.php
│   │   ├── Assignment/
│   │   │   ├── AutoAssignConversationAction.php
│   │   │   ├── ManualAssignConversationAction.php
│   │   │   └── UnassignConversationAction.php
│   │   └── Automation/
│   │       ├── ProcessAutomationRuleAction.php
│   │       └── EvaluateConditionsAction.php
│   │
│   ├── Broadcasting/                     # Laravel Reverb Channels
│   │   ├── Conversation/
│   │   │   ├── ConversationChannel.php
│   │   │   └── ConversationPresenceChannel.php
│   │   ├── Message/
│   │   │   └── MessageChannel.php
│   │   └── Presence/
│   │       ├── AccountPresenceChannel.php
│   │       └── AgentPresenceChannel.php
│   │
│   ├── Data/                             # Spatie Data DTOs (Type-safe Data Transfer Objects)
│   │   ├── Account/
│   │   │   ├── AccountData.php
│   │   │   └── AccountSettingsData.php
│   │   ├── Conversation/
│   │   │   ├── ConversationData.php
│   │   │   ├── ConversationFilterData.php
│   │   │   └── ConversationStatsData.php
│   │   ├── Message/
│   │   │   ├── MessageData.php
│   │   │   ├── AttachmentData.php
│   │   │   └── MessageContentData.php
│   │   ├── Contact/
│   │   │   ├── ContactData.php
│   │   │   └── ContactAttributesData.php
│   │   └── Inbox/
│   │       ├── InboxData.php
│   │       └── InboxSettingsData.php
│   │
│   ├── Events/                           # Laravel Events
│   │   ├── Conversation/
│   │   │   ├── ConversationCreated.php
│   │   │   ├── ConversationAssigned.php
│   │   │   ├── ConversationStatusChanged.php
│   │   │   └── ConversationResolved.php
│   │   ├── Message/
│   │   │   ├── MessageCreated.php
│   │   │   ├── MessageUpdated.php
│   │   │   └── MessageDeleted.php
│   │   └── Contact/
│   │       ├── ContactCreated.php
│   │       ├── ContactUpdated.php
│   │       └── ContactMerged.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── AccountsController.php
│   │   │   │   │   ├── ConversationsController.php
│   │   │   │   │   ├── MessagesController.php
│   │   │   │   │   ├── ContactsController.php
│   │   │   │   │   ├── InboxesController.php
│   │   │   │   │   ├── AgentsController.php
│   │   │   │   │   ├── TeamMembersController.php
│   │   │   │   │   └── NotificationsController.php
│   │   │   │   └── Webhooks/
│   │   │   │       ├── FacebookController.php
│   │   │   │       ├── TwilioController.php
│   │   │   │       └── GenericWebhookController.php
│   │   │   └── Auth/
│   │   │       ├── LoginController.php
│   │   │       ├── RegisterController.php
│   │   │       └── LogoutController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── EnsureAccountAccess.php
│   │   │   ├── EnsureInboxAccess.php
│   │   │   └── LogActivity.php
│   │   │
│   │   ├── Requests/                     # Form Requests (Validation & Authorization)
│   │   │   ├── Account/
│   │   │   │   ├── StoreAccountRequest.php
│   │   │   │   └── UpdateAccountRequest.php
│   │   │   ├── Conversation/
│   │   │   │   ├── StoreConversationRequest.php
│   │   │   │   ├── UpdateConversationRequest.php
│   │   │   │   └── AssignConversationRequest.php
│   │   │   ├── Message/
│   │   │   │   ├── StoreMessageRequest.php
│   │   │   │   └── UpdateMessageRequest.php
│   │   │   ├── Contact/
│   │   │   │   ├── StoreContactRequest.php
│   │   │   │   ├── UpdateContactRequest.php
│   │   │   │   └── MergeContactsRequest.php
│   │   │   └── Inbox/
│   │   │       ├── StoreInboxRequest.php
│   │   │       └── UpdateInboxRequest.php
│   │   │
│   │   └── Resources/                    # API Resources (Response Formatting)
│   │       ├── Account/
│   │       │   ├── AccountResource.php
│   │       │   └── AccountCollection.php
│   │       ├── Conversation/
│   │       │   ├── ConversationResource.php
│   │       │   ├── ConversationCollection.php
│   │       │   └── ConversationStatsResource.php
│   │       ├── Message/
│   │       │   ├── MessageResource.php
│   │       │   └── MessageCollection.php
│   │       ├── Contact/
│   │       │   ├── ContactResource.php
│   │       │   └── ContactCollection.php
│   │       └── Inbox/
│   │           ├── InboxResource.php
│   │           └── InboxCollection.php
│   │
│   ├── Jobs/                             # Queue Jobs (Horizon)
│   │   ├── Conversation/
│   │   │   ├── AutoResolveConversationJob.php
│   │   │   └── UpdateConversationMetricsJob.php
│   │   ├── Message/
│   │   │   ├── ProcessIncomingMessageJob.php
│   │   │   └── ProcessOutgoingMessageJob.php
│   │   ├── Assignment/
│   │   │   ├── AutoAssignConversationsJob.php
│   │   │   └── RebalanceAssignmentsJob.php
│   │   └── Notification/
│   │       ├── SendEmailNotificationJob.php
│   │       ├── SendPushNotificationJob.php
│   │       └── SendWebhookNotificationJob.php
│   │
│   ├── Listeners/                        # Event Listeners
│   │   ├── Conversation/
│   │   │   ├── NotifyAgentOnAssignment.php
│   │   │   ├── UpdateConversationMetrics.php
│   │   │   └── BroadcastConversationUpdate.php
│   │   ├── Message/
│   │   │   ├── BroadcastNewMessage.php
│   │   │   ├── ProcessMessageAttachments.php
│   │   │   └── TriggerAutomationRules.php
│   │   └── Contact/
│   │       ├── UpdateContactMetrics.php
│   │       └── SyncContactToExternalServices.php
│   │
│   ├── Models/                           # Eloquent Models
│   │   ├── Account.php
│   │   ├── User.php
│   │   ├── Conversation.php
│   │   ├── Message.php
│   │   ├── Contact.php
│   │   ├── ContactInbox.php
│   │   ├── Inbox.php
│   │   ├── Channel.php                  # Polymorphic base
│   │   ├── Channels/
│   │   │   ├── WebChannel.php
│   │   │   ├── EmailChannel.php
│   │   │   ├── TwilioChannel.php
│   │   │   └── FacebookChannel.php
│   │   ├── AgentBot.php
│   │   ├── AutomationRule.php
│   │   ├── CannedResponse.php
│   │   ├── Label.php
│   │   ├── Team.php
│   │   ├── TeamMember.php
│   │   ├── Notification.php
│   │   └── Webhook.php
│   │
│   ├── Policies/                         # Authorization Policies
│   │   ├── AccountPolicy.php
│   │   ├── ConversationPolicy.php
│   │   ├── MessagePolicy.php
│   │   ├── ContactPolicy.php
│   │   ├── InboxPolicy.php
│   │   └── TeamPolicy.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php       # Register policies
│   │   ├── EventServiceProvider.php      # Register events & listeners
│   │   ├── BroadcastServiceProvider.php  # Reverb channels
│   │   └── HorizonServiceProvider.php    # Queue dashboard
│   │
│   └── Repositories/                     # Repository Pattern (Data Access Layer)
│       ├── Account/
│       │   └── AccountRepository.php
│       ├── Conversation/
│       │   ├── ConversationRepository.php
│       │   └── ConversationFilterRepository.php
│       ├── Message/
│       │   └── MessageRepository.php
│       ├── Contact/
│       │   └── ContactRepository.php
│       └── Inbox/
│           └── InboxRepository.php
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── broadcasting.php                  # Reverb configuration
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── horizon.php                       # Queue dashboard config
│   ├── logging.php
│   ├── mail.php
│   ├── permission.php                    # Spatie Permission config
│   ├── queue.php
│   ├── reverb.php                        # Laravel Reverb config
│   ├── sanctum.php                       # API authentication
│   └── services.php
│
├── database/
│   ├── factories/
│   │   ├── AccountFactory.php
│   │   ├── UserFactory.php
│   │   ├── ConversationFactory.php
│   │   ├── MessageFactory.php
│   │   ├── ContactFactory.php
│   │   └── InboxFactory.php
│   │
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_accounts_table.php
│   │   ├── 2024_01_01_000002_create_users_table.php
│   │   ├── 2024_01_01_000003_create_contacts_table.php
│   │   ├── 2024_01_01_000004_create_inboxes_table.php
│   │   ├── 2024_01_01_000005_create_channels_table.php
│   │   ├── 2024_01_01_000006_create_contact_inboxes_table.php
│   │   ├── 2024_01_01_000007_create_conversations_table.php
│   │   ├── 2024_01_01_000008_create_messages_table.php
│   │   ├── 2024_01_01_000009_create_teams_table.php
│   │   ├── 2024_01_01_000010_create_team_members_table.php
│   │   ├── 2024_01_01_000011_create_labels_table.php
│   │   ├── 2024_01_01_000012_create_automation_rules_table.php
│   │   ├── 2024_01_01_000013_create_canned_responses_table.php
│   │   ├── 2024_01_01_000014_create_webhooks_table.php
│   │   ├── 2024_01_01_000015_create_notifications_table.php
│   │   ├── 2024_01_01_000016_create_activity_log_table.php       # Spatie Activity Log
│   │   ├── 2024_01_01_000017_create_permission_tables.php         # Spatie Permission
│   │   └── 2024_01_01_000018_create_personal_access_tokens_table.php  # Sanctum
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── AccountSeeder.php
│       ├── UserSeeder.php
│       ├── RolesAndPermissionsSeeder.php
│       └── DemoDataSeeder.php
│
├── public/
│   └── index.php
│
├── resources/
│   ├── js/
│   │   └── app.js                        # Vue 3 + Reverb Echo setup
│   ├── css/
│   │   └── app.css
│   └── views/
│       └── welcome.blade.php
│
├── routes/
│   ├── api.php                           # API routes
│   ├── channels.php                      # Reverb broadcast channels
│   ├── console.php                       # Artisan commands
│   └── web.php                           # Web routes
│
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
│
├── tests/
│   ├── Feature/
│   │   ├── Api/
│   │   │   ├── AccountsTest.php
│   │   │   ├── ConversationsTest.php
│   │   │   ├── MessagesTest.php
│   │   │   ├── ContactsTest.php
│   │   │   └── InboxesTest.php
│   │   ├── Broadcasting/
│   │   │   ├── ConversationChannelTest.php
│   │   │   └── PresenceChannelTest.php
│   │   └── Actions/
│   │       ├── AutoAssignConversationActionTest.php
│   │       └── CreateMessageActionTest.php
│   │
│   ├── Unit/
│   │   ├── Models/
│   │   │   ├── AccountTest.php
│   │   │   ├── ConversationTest.php
│   │   │   └── MessageTest.php
│   │   ├── Repositories/
│   │   │   └── ConversationRepositoryTest.php
│   │   └── Actions/
│   │       └── AutoAssignLogicTest.php
│   │
│   ├── Pest.php                          # Pest configuration
│   └── TestCase.php
│
├── .env.example                          # Environment configuration
├── .gitignore
├── artisan                               # Laravel CLI
├── composer.json
├── composer.lock
├── FOLDER_STRUCTURE.md                   # This file
├── TASKS.md                              # Task checklist
├── package.json                          # Node dependencies (for Vue 3 + Reverb)
├── phpunit.xml
├── README.md
└── vite.config.js                        # Frontend build config
```

## 🎯 Key Architectural Decisions

### 1. **Actions (Lorisleiva Laravel Actions)**
- Replace traditional Service classes
- Can run as controller, job, command, or listener
- Single responsibility principle
- Constructor dependency injection
- Type-hinted parameters and return types

### 2. **Data DTOs (Spatie Data)**
- Type-safe data transfer objects
- Automatic validation
- JSON serialization
- Immutable by default
- Better than plain arrays

### 3. **Repository Pattern**
- Abstract data access from business logic
- Easier to test with mocks
- Centralized query logic
- Can switch data sources easily

### 4. **Laravel Reverb**
- First-party WebSocket server
- Zero external dependencies
- No Pusher/Ably costs
- Presence channels built-in
- Horizontal scaling with Redis

### 5. **Multi-Tenancy**
- Account-based isolation
- Global scopes on models
- Middleware for access control
- Separate data per account

### 6. **Event-Driven Architecture**
- Decouple business logic
- Trigger multiple actions from single event
- Easy to add new features
- Async processing with queue listeners

## 📦 Key Packages Installed

- **laravel/framework**: ^12.0 (Latest)
- **laravel/sanctum**: ^4.2 (API Authentication)
- **laravel/horizon**: ^5.41 (Queue Dashboard)
- **laravel/reverb**: ^1.6 (WebSocket Server)
- **lorisleiva/laravel-actions**: ^2.9 (Action Pattern)
- **spatie/laravel-data**: ^4.18 (Type-safe DTOs)
- **spatie/laravel-activitylog**: ^4.10 (Audit Trail)
- **spatie/laravel-permission**: ^6.24 (Roles & Permissions)
- **pestphp/pest**: ^3.8 (Testing Framework)
- **pestphp/pest-plugin-laravel**: ^3.2 (Laravel Test Helpers)

## 🚀 Next Steps

1. Review `TASKS.md` for complete migration checklist
2. Set up `.env` configuration
3. Run migrations: `php artisan migrate`
4. Start Reverb: `php artisan reverb:start`
5. Start Horizon: `php artisan horizon`
6. Run tests: `php artisan test`

## 📚 Documentation References

- [Custom Docs: Backend Architecture](../docs/BACKEND_ARCHITECTURE.md)
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Reverb](https://laravel.com/docs/12.x/reverb)
- [Lorisleiva Actions](https://laravelactions.com)
- [Spatie Data](https://spatie.be/docs/laravel-data)
