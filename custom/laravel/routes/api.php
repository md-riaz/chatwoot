<?php


use App\Http\Controllers\Api\V1\AccountsController;
use App\Http\Controllers\Api\V1\AgentBotsController;
use App\Http\Controllers\Api\V1\AgentsController;
use App\Http\Controllers\Api\V1\ArticlesController;
use App\Http\Controllers\Api\V1\AttachmentsController;
use App\Http\Controllers\Api\V1\AuditLogsController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\AutomationRulesController;
use App\Http\Controllers\Api\V1\BulkActionsController;
use App\Http\Controllers\Api\V1\CampaignsController;
use App\Http\Controllers\Api\V1\CannedResponsesController;
use App\Http\Controllers\Api\V1\CategoriesController;
use App\Http\Controllers\Api\V1\Channels\ApiController as ChannelApiController;
use App\Http\Controllers\Api\V1\Channels\EmailController;
use App\Http\Controllers\Api\V1\Channels\FacebookController;
use App\Http\Controllers\Api\V1\Channels\LineController;
use App\Http\Controllers\Api\V1\Channels\SmsController;
use App\Http\Controllers\Api\V1\Channels\TelegramController;
use App\Http\Controllers\Api\V1\Channels\TwitterController;
use App\Http\Controllers\Api\V1\Channels\WebWidgetController;
use App\Http\Controllers\Api\V1\Channels\WhatsAppController;
use App\Http\Controllers\Api\V1\ContactNotesController;
use App\Http\Controllers\Api\V1\ContactsController;
use App\Http\Controllers\Api\V1\ConversationsController;
use App\Http\Controllers\Api\V1\CsatSurveyResponsesController;
use App\Http\Controllers\Api\V1\CustomAttributeDefinitionsController;
use App\Http\Controllers\Api\V1\CustomFiltersController;
use App\Http\Controllers\Api\V1\DashboardAppsController;
use App\Http\Controllers\Api\V1\InboxesController;
use App\Http\Controllers\Api\V1\Integrations\DialogflowController;
use App\Http\Controllers\Api\V1\Integrations\IntegrationsController;
use App\Http\Controllers\Api\V1\Integrations\LinearController;
use App\Http\Controllers\Api\V1\Integrations\OpenAIController;
use App\Http\Controllers\Api\V1\Integrations\ShopifyController;
use App\Http\Controllers\Api\V1\Integrations\SlackController;
use App\Http\Controllers\Api\V1\LabelsController;
use App\Http\Controllers\Api\V1\MacrosController;
use App\Http\Controllers\Api\V1\MessagesController;
use App\Http\Controllers\Api\V1\NotificationsController;
use App\Http\Controllers\Api\V1\PortalsController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SegmentsController;
use App\Http\Controllers\Api\V1\SlaPoliciesController;
use App\Http\Controllers\Api\V1\SuperAdmin\AccessTokensController as SuperAdminAccessTokensController;
use App\Http\Controllers\Api\V1\SuperAdmin\AccountsController as SuperAdminAccountsController;
use App\Http\Controllers\Api\V1\SuperAdmin\AgentBotsController as SuperAdminAgentBotsController;
use App\Http\Controllers\Api\V1\SuperAdmin\InstallationConfigsController;
use App\Http\Controllers\Api\V1\SuperAdmin\InstanceStatusController;
use App\Http\Controllers\Api\V1\SuperAdmin\PlatformAppsController;
use App\Http\Controllers\Api\V1\SuperAdmin\UsersController as SuperAdminUsersController;
use App\Http\Controllers\Api\V1\TeamsController;
use App\Http\Controllers\Api\V1\UsersController;
use App\Http\Controllers\Api\V1\WebhooksController;
use App\Http\Controllers\Api\V1\WorkingHoursController;
// Widget Controllers
use App\Http\Controllers\Api\V1\Widget\ConfigsController as WidgetConfigsController;
use App\Http\Controllers\Api\V1\Widget\ContactsController as WidgetContactsController;
use App\Http\Controllers\Api\V1\Widget\ConversationsController as WidgetConversationsController;
use App\Http\Controllers\Api\V1\Widget\MessagesController as WidgetMessagesController;
use App\Http\Controllers\Api\V1\Widget\CampaignsController as WidgetCampaignsController;
use App\Http\Controllers\Api\V1\Widget\LabelsController as WidgetLabelsController;
use App\Http\Controllers\Api\V1\Widget\InboxMembersController as WidgetInboxMembersController;
use App\Http\Controllers\Api\V1\Widget\EventsController as WidgetEventsController;
use App\Http\Controllers\Api\V1\Widget\DirectUploadsController as WidgetDirectUploadsController;
// Platform Controllers
use App\Http\Controllers\Api\V1\Platform\UsersController as PlatformUsersController;
use App\Http\Controllers\Api\V1\Platform\AccountsController as PlatformAccountsController;
use App\Http\Controllers\Api\V1\Platform\AccountUsersController as PlatformAccountUsersController;
use App\Http\Controllers\Api\V1\Platform\AgentBotsController as PlatformAgentBotsController;
// Public Inbox Controllers
use App\Http\Controllers\Api\V1\Public\Inboxes\ContactsController as PublicContactsController;
use App\Http\Controllers\Api\V1\Public\Inboxes\ConversationsController as PublicConversationsController;
use App\Http\Controllers\Api\V1\Public\Inboxes\MessagesController as PublicMessagesController;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Controllers\Api\V1\NotificationSubscriptionsController;
use App\Http\Controllers\Api\V1\Profile\MfaController;
// New controllers for missing APIs
use App\Http\Controllers\Api\V1\CompaniesController;
use App\Http\Controllers\Api\V1\CustomRolesController;
use App\Http\Controllers\Api\V1\AssignmentPoliciesController;
use App\Http\Controllers\Api\V1\AgentCapacityPoliciesController;
use App\Http\Controllers\Api\V1\NotificationSettingsController;
use App\Http\Controllers\Api\V1\SamlSettingsController;
use App\Http\Controllers\Api\V1\Channels\InstagramController;
use App\Http\Controllers\Api\V1\Channels\VoiceController;
use App\Http\Controllers\Api\V1\Conversations\ParticipantsController;
use App\Http\Controllers\Api\V1\Conversations\DraftMessagesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. The apiPrefix is set to
| "api/v1" in bootstrap/app.php.
|
*/

// Public routes
Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'api_version' => 'v1',
    ]);
});


// Onboarding route for first superadmin creation (Rails-style)
Route::post('installation/onboarding', [\App\Http\Controllers\Api\V1\InstallationOnboardingController::class, 'onboard']);

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
});

// Webhook routes (public)
Route::prefix('webhooks')->group(function () {
    // WhatsApp webhooks
    Route::get('whatsapp', [WhatsAppController::class, 'verifyWebhook']);
    Route::post('whatsapp', [WhatsAppController::class, 'webhook']);
    
    // Facebook webhooks
    Route::get('facebook', [FacebookController::class, 'verifyWebhook']);
    Route::post('facebook', [FacebookController::class, 'webhook']);
    
    // Telegram webhooks
    Route::post('telegram/{inboxId}', [TelegramController::class, 'webhook']);
    
    // Twitter webhooks
    Route::get('twitter', [TwitterController::class, 'crcCheck']);
    Route::post('twitter', [TwitterController::class, 'webhook']);
    
    // Email inbound
    Route::post('email', [EmailController::class, 'inbound']);
    
    // SMS webhooks
    Route::post('sms', [SmsController::class, 'webhook']);
    
    // Line webhooks
    Route::post('line', [LineController::class, 'webhook']);
    
    // Slack webhooks
    Route::post('slack/events', [SlackController::class, 'events']);
    Route::post('slack/interactive', [SlackController::class, 'interactive']);
    Route::post('slack/commands', [SlackController::class, 'commands']);
    
    // Shopify webhooks
    Route::post('shopify', [ShopifyController::class, 'webhook']);
    
    // Instagram webhooks
    Route::get('instagram', [InstagramController::class, 'verifyWebhook']);
    Route::post('instagram', [InstagramController::class, 'webhook']);
    
    // Voice webhooks (Twilio)
    Route::post('voice/call/{phone}', [VoiceController::class, 'callTwiml']);
    Route::post('voice/status/{phone}', [VoiceController::class, 'status']);
    Route::post('voice/conference_status/{phone}', [VoiceController::class, 'conferenceStatus']);
});

// Public CSAT Survey routes (no auth required)
Route::prefix('public')->group(function () {
    Route::get('csat/{uuid}', [App\Http\Controllers\Api\V1\Public\CsatSurveyController::class, 'show']);
    Route::post('csat/{uuid}', [App\Http\Controllers\Api\V1\Public\CsatSurveyController::class, 'update']);
    
    // Public API for inboxes (no auth required, uses inbox identifier)
    Route::prefix('inboxes/{inbox}')->group(function () {
        Route::post('contacts', [PublicContactsController::class, 'store']);
        Route::get('contacts/{contact}', [PublicContactsController::class, 'show']);
        Route::patch('contacts/{contact}', [PublicContactsController::class, 'update']);
        
        Route::get('contacts/{contact}/conversations', [PublicConversationsController::class, 'index']);
        Route::post('contacts/{contact}/conversations', [PublicConversationsController::class, 'store']);
        Route::get('contacts/{contact}/conversations/{conversation}', [PublicConversationsController::class, 'show']);
        Route::post('contacts/{contact}/conversations/{conversation}/toggle_status', [PublicConversationsController::class, 'toggleStatus']);
        Route::post('contacts/{contact}/conversations/{conversation}/toggle_typing', [PublicConversationsController::class, 'toggleTyping']);
        Route::post('contacts/{contact}/conversations/{conversation}/update_last_seen', [PublicConversationsController::class, 'updateLastSeen']);
        
        Route::get('contacts/{contact}/conversations/{conversation}/messages', [PublicMessagesController::class, 'index']);
        Route::post('contacts/{contact}/conversations/{conversation}/messages', [PublicMessagesController::class, 'store']);
        Route::patch('contacts/{contact}/conversations/{conversation}/messages/{message}', [PublicMessagesController::class, 'update']);
    });
});

// Widget API routes (public, uses X-Auth-Token header)
Route::prefix('widget')->group(function () {
    Route::post('config', [WidgetConfigsController::class, 'create']);
    Route::get('campaigns', [WidgetCampaignsController::class, 'index']);
    
    // Routes requiring widget token authentication
    Route::get('contact', [WidgetContactsController::class, 'show']);
    Route::patch('contact', [WidgetContactsController::class, 'update']);
    Route::post('contact/destroy_custom_attributes', [WidgetContactsController::class, 'destroyCustomAttributes']);
    Route::patch('contact/set_user', [WidgetContactsController::class, 'setUser']);
    
    Route::get('conversations', [WidgetConversationsController::class, 'index']);
    Route::post('conversations', [WidgetConversationsController::class, 'create']);
    Route::get('conversations/toggle_status', [WidgetConversationsController::class, 'toggleStatus']);
    Route::post('conversations/toggle_typing', [WidgetConversationsController::class, 'toggleTyping']);
    Route::post('conversations/update_last_seen', [WidgetConversationsController::class, 'updateLastSeen']);
    Route::post('conversations/set_custom_attributes', [WidgetConversationsController::class, 'setCustomAttributes']);
    Route::post('conversations/destroy_custom_attributes', [WidgetConversationsController::class, 'destroyCustomAttributes']);
    Route::post('conversations/transcript', [WidgetConversationsController::class, 'transcript']);
    
    Route::get('messages', [WidgetMessagesController::class, 'index']);
    Route::post('messages', [WidgetMessagesController::class, 'store']);
    Route::patch('messages/{message}', [WidgetMessagesController::class, 'update']);
    
    Route::get('inbox_members', [WidgetInboxMembersController::class, 'index']);
    
    Route::post('labels', [WidgetLabelsController::class, 'store']);
    Route::delete('labels/{label}', [WidgetLabelsController::class, 'destroy']);
    
    Route::post('events', [WidgetEventsController::class, 'store']);
    
    Route::post('direct_uploads', [WidgetDirectUploadsController::class, 'store']);
});

// Platform API routes (for platform-level integrations)
Route::prefix('platform')->group(function () {
    Route::get('users/{user}', [PlatformUsersController::class, 'show']);
    Route::post('users', [PlatformUsersController::class, 'store']);
    Route::patch('users/{user}', [PlatformUsersController::class, 'update']);
    Route::delete('users/{user}', [PlatformUsersController::class, 'destroy']);
    Route::get('users/{user}/login', [PlatformUsersController::class, 'login']);
    Route::post('users/{user}/token', [PlatformUsersController::class, 'token']);
    
    Route::get('accounts', [PlatformAccountsController::class, 'index']);
    Route::get('accounts/{account}', [PlatformAccountsController::class, 'show']);
    Route::post('accounts', [PlatformAccountsController::class, 'store']);
    Route::patch('accounts/{account}', [PlatformAccountsController::class, 'update']);
    Route::delete('accounts/{account}', [PlatformAccountsController::class, 'destroy']);
    
    Route::get('accounts/{account}/account_users', [PlatformAccountUsersController::class, 'index']);
    Route::post('accounts/{account}/account_users', [PlatformAccountUsersController::class, 'store']);
    Route::delete('accounts/{account}/account_users', [PlatformAccountUsersController::class, 'destroy']);
    
    Route::get('agent_bots', [PlatformAgentBotsController::class, 'index']);
    Route::get('agent_bots/{agentBot}', [PlatformAgentBotsController::class, 'show']);
    Route::post('agent_bots', [PlatformAgentBotsController::class, 'store']);
    Route::patch('agent_bots/{agentBot}', [PlatformAgentBotsController::class, 'update']);
    Route::delete('agent_bots/{agentBot}', [PlatformAgentBotsController::class, 'destroy']);
    Route::delete('agent_bots/{agentBot}/avatar', [PlatformAgentBotsController::class, 'avatar']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
        Route::get('me', [LoginController::class, 'me']);
    });

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::patch('/', [ProfileController::class, 'update']);
        Route::patch('password', [ProfileController::class, 'updatePassword']);
        Route::patch('availability', [ProfileController::class, 'updateAvailability']);
        Route::patch('auto_offline', [ProfileController::class, 'updateAutoOffline']);
        Route::delete('avatar', [ProfileController::class, 'avatar']);
        Route::put('set_active_account', [ProfileController::class, 'setActiveAccount']);
        Route::post('resend_confirmation', [ProfileController::class, 'resendConfirmation']);
        Route::post('reset_access_token', [ProfileController::class, 'resetAccessToken']);
        
        // MFA routes
        Route::prefix('mfa')->group(function () {
            Route::get('/', [MfaController::class, 'show']);
            Route::post('/', [MfaController::class, 'store']);
            Route::delete('/', [MfaController::class, 'destroy']);
            Route::post('verify', [MfaController::class, 'verify']);
            Route::post('backup_codes', [MfaController::class, 'backupCodes']);
        });
    });

    // Notification Subscriptions
    Route::post('notification_subscriptions', [NotificationSubscriptionsController::class, 'store']);
    Route::delete('notification_subscriptions', [NotificationSubscriptionsController::class, 'destroy']);

    // Notifications routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationsController::class, 'index']);
        Route::get('unread_count', [NotificationsController::class, 'unreadCount']);
        Route::post('{notification}/read', [NotificationsController::class, 'markAsRead']);
        Route::post('read_all', [NotificationsController::class, 'markAllAsRead']);
        Route::delete('{notification}', [NotificationsController::class, 'destroy']);
        Route::delete('/', [NotificationsController::class, 'destroyAll']);
    });

    // Account routes
    Route::apiResource('accounts', AccountsController::class);

    // Account-scoped resources (with account access middleware)
    Route::prefix('accounts/{account}')->middleware(\App\Http\Middleware\EnsureAccountAccess::class)->group(function () {
        // Conversations
        Route::apiResource('conversations', ConversationsController::class);
        Route::get('conversations/meta', [ConversationsController::class, 'meta']);
        Route::get('conversations/search', [ConversationsController::class, 'search']);
        Route::post('conversations/filter', [ConversationsController::class, 'filter']);
        Route::post('conversations/{conversation}/assign', [ConversationsController::class, 'assign']);
        Route::post('conversations/{conversation}/toggle_status', [ConversationsController::class, 'toggleStatus']);
        Route::post('conversations/{conversation}/resolve', [ConversationsController::class, 'resolve']);
        Route::post('conversations/{conversation}/mute', [ConversationsController::class, 'mute']);
        Route::post('conversations/{conversation}/unmute', [ConversationsController::class, 'unmute']);
        Route::post('conversations/{conversation}/transcript', [ConversationsController::class, 'transcript']);
        Route::post('conversations/{conversation}/toggle_priority', [ConversationsController::class, 'togglePriority']);
        Route::post('conversations/{conversation}/labels', [ConversationsController::class, 'addLabels']);
        Route::delete('conversations/{conversation}/labels', [ConversationsController::class, 'removeLabels']);
        Route::post('conversations/{conversation}/toggle_typing_status', [ConversationsController::class, 'toggleTypingStatus']);
        Route::post('conversations/{conversation}/update_last_seen', [ConversationsController::class, 'updateLastSeen']);
        Route::post('conversations/{conversation}/unread', [ConversationsController::class, 'unread']);
        Route::post('conversations/{conversation}/custom_attributes', [ConversationsController::class, 'customAttributes']);
        Route::get('conversations/{conversation}/attachments', [ConversationsController::class, 'attachments']);

        // Messages (nested under conversations)
        Route::apiResource('conversations/{conversation}/messages', MessagesController::class);
        Route::post('conversations/{conversation}/messages/{message}/translate', [MessagesController::class, 'translate']);
        Route::post('conversations/{conversation}/messages/{message}/retry', [MessagesController::class, 'retry']);
        
        // Conversation Participants
        Route::get('conversations/{conversation}/participants', [ParticipantsController::class, 'show']);
        Route::post('conversations/{conversation}/participants', [ParticipantsController::class, 'store']);
        Route::patch('conversations/{conversation}/participants', [ParticipantsController::class, 'update']);
        Route::delete('conversations/{conversation}/participants', [ParticipantsController::class, 'destroy']);
        
        // Draft Messages
        Route::get('conversations/{conversation}/draft_messages', [DraftMessagesController::class, 'show']);
        Route::patch('conversations/{conversation}/draft_messages', [DraftMessagesController::class, 'update']);
        Route::delete('conversations/{conversation}/draft_messages', [DraftMessagesController::class, 'destroy']);
        
        // Attachments (nested under conversations)
        Route::apiResource('conversations/{conversation}/attachments', AttachmentsController::class)
            ->only(['index', 'store', 'show', 'destroy']);

        // Contacts
        Route::apiResource('contacts', ContactsController::class);
        Route::get('contacts/search', [ContactsController::class, 'search']);
        Route::get('contacts/active', [ContactsController::class, 'active']);
        Route::post('contacts/filter', [ContactsController::class, 'filter']);
        Route::post('contacts/import', [ContactsController::class, 'import']);
        Route::post('contacts/export', [ContactsController::class, 'export']);
        Route::post('contacts/{contact}/merge', [ContactsController::class, 'merge']);
        Route::get('contacts/{contact}/contactable_inboxes', [ContactsController::class, 'contactableInboxes']);
        Route::post('contacts/{contact}/destroy_custom_attributes', [ContactsController::class, 'destroyCustomAttributes']);
        Route::delete('contacts/{contact}/avatar', [ContactsController::class, 'avatar']);
        
        // Contact Notes
        Route::apiResource('contacts/{contact}/notes', ContactNotesController::class);

        // Inboxes
        Route::apiResource('inboxes', InboxesController::class);
        Route::get('inboxes/{inbox}/members', [InboxesController::class, 'members']);
        Route::post('inboxes/{inbox}/members', [InboxesController::class, 'addMember']);
        Route::delete('inboxes/{inbox}/members', [InboxesController::class, 'removeMember']);
        Route::get('inboxes/{inbox}/assignable_agents', [InboxesController::class, 'assignableAgents']);
        Route::get('inboxes/{inbox}/campaigns', [InboxesController::class, 'campaigns']);
        Route::delete('inboxes/{inbox}/avatar', [InboxesController::class, 'avatar']);
        Route::get('inboxes/{inbox}/agent_bot', [InboxesController::class, 'agentBot']);
        Route::post('inboxes/{inbox}/set_agent_bot', [InboxesController::class, 'setAgentBot']);
        Route::post('inboxes/{inbox}/sync_templates', [InboxesController::class, 'syncTemplates']);
        Route::get('inboxes/{inbox}/message_templates', [InboxesController::class, 'messageTemplates']);
        Route::get('inboxes/{inbox}/health', [InboxesController::class, 'health']);
        
        // Working Hours
        Route::get('inboxes/{inbox}/working_hours', [WorkingHoursController::class, 'index']);
        Route::match(['put', 'patch'], 'inboxes/{inbox}/working_hours', [WorkingHoursController::class, 'update']);
        Route::get('inboxes/{inbox}/is_open', [WorkingHoursController::class, 'isOpen']);

        // Teams
        Route::apiResource('teams', TeamsController::class);
        Route::get('teams/{team}/members', [TeamsController::class, 'members']);
        Route::post('teams/{team}/members', [TeamsController::class, 'addMember']);
        Route::delete('teams/{team}/members', [TeamsController::class, 'removeMember']);

        // Labels
        Route::apiResource('labels', LabelsController::class);

        // Webhooks
        Route::apiResource('webhooks', WebhooksController::class);

        // Canned Responses
        Route::apiResource('canned_responses', CannedResponsesController::class);

        // Campaigns
        Route::apiResource('campaigns', CampaignsController::class);

        // Automation Rules
        Route::apiResource('automation_rules', AutomationRulesController::class);
        Route::post('automation_rules/{automation_rule}/clone', [AutomationRulesController::class, 'clone']);

        // Custom Filters
        Route::apiResource('custom_filters', CustomFiltersController::class);

        // Custom Attribute Definitions
        Route::apiResource('custom_attribute_definitions', CustomAttributeDefinitionsController::class);

        // Agent Bots
        Route::apiResource('agent_bots', AgentBotsController::class);

        // Macros
        Route::apiResource('macros', MacrosController::class);
        Route::post('macros/{macro}/execute', [MacrosController::class, 'execute']);

        // Dashboard Apps
        Route::apiResource('dashboard_apps', DashboardAppsController::class);

        // Users (Agents)
        Route::apiResource('users', UsersController::class);
        Route::apiResource('agents', AgentsController::class);
        Route::post('agents/bulk_create', [AgentsController::class, 'bulkCreate']);

        // Portals (Help Center)
        Route::apiResource('portals', PortalsController::class);
        Route::get('portals/{portal}/articles', [PortalsController::class, 'articles']);
        Route::get('portals/{portal}/categories', [PortalsController::class, 'categories']);
        
        // Articles (nested under portals)
        Route::apiResource('portals/{portal}/articles', ArticlesController::class);
        
        // Categories (nested under portals)
        Route::apiResource('portals/{portal}/categories', CategoriesController::class);

        // CSAT Survey Responses
        Route::get('csat_survey_responses', [CsatSurveyResponsesController::class, 'index']);
        Route::get('csat_survey_responses/metrics', [CsatSurveyResponsesController::class, 'metrics']);
        Route::get('csat_survey_responses/download', [CsatSurveyResponsesController::class, 'download']);
        Route::get('csat_survey_responses/{csat_survey_response}', [CsatSurveyResponsesController::class, 'show']);

        // Segments
        Route::apiResource('segments', SegmentsController::class);
        Route::get('segments/{segment}/contacts', [SegmentsController::class, 'contacts']);
        Route::get('segments/{segment}/count', [SegmentsController::class, 'count']);

        // Search
        Route::get('search', [SearchController::class, 'index']);
        Route::get('search/conversations', [SearchController::class, 'conversations']);
        Route::get('search/contacts', [SearchController::class, 'contacts']);
        Route::get('search/messages', [SearchController::class, 'messages']);

        // Bulk Actions (matches Chatwoot Rails API - single resource with create action)
        Route::post('bulk_actions', [BulkActionsController::class, 'store']);
        Route::post('bulk_actions/conversations', [BulkActionsController::class, 'conversations']);
        Route::delete('bulk_actions/conversations', [BulkActionsController::class, 'deleteConversations']);

        // Reports
        Route::get('reports', [ReportsController::class, 'index']);
        Route::get('reports/conversations', [ReportsController::class, 'conversations']);
        Route::get('reports/agents', [ReportsController::class, 'agents']);
        Route::get('reports/inboxes', [ReportsController::class, 'inboxes']);
        Route::get('reports/teams', [ReportsController::class, 'teams']);
        Route::get('reports/labels', [ReportsController::class, 'labels']);
        Route::get('reports/download', [ReportsController::class, 'download']);

        // SLA Policies
        Route::apiResource('sla_policies', SlaPoliciesController::class);
        Route::get('sla_policies/breaches', [SlaPoliciesController::class, 'breaches']);
        Route::get('sla_policies/metrics', [SlaPoliciesController::class, 'metrics']);

        // Audit Logs
        Route::get('audit_logs', [AuditLogsController::class, 'index']);
        Route::get('audit_logs/summary', [AuditLogsController::class, 'summary']);
        Route::get('audit_logs/download', [AuditLogsController::class, 'download']);
        Route::get('audit_logs/export', [AuditLogsController::class, 'download']);
        Route::get('audit_logs/{log}', [AuditLogsController::class, 'show']);
        Route::get('audit_logs/{type}/{id}', [AuditLogsController::class, 'forResource']);

        // Working Hours (account level)
        Route::get('working_hours', [WorkingHoursController::class, 'accountSettings']);
        Route::patch('working_hours', [WorkingHoursController::class, 'updateAccountSettings']);

        // Channel Integrations
        Route::prefix('channels')->group(function () {
            // WhatsApp
            Route::post('whatsapp', [WhatsAppController::class, 'create']);
            Route::patch('whatsapp/{inbox}', [WhatsAppController::class, 'update']);
            Route::post('whatsapp/{inbox}/send_template', [WhatsAppController::class, 'sendTemplate']);
            Route::post('whatsapp/{inbox}/sync_templates', [WhatsAppController::class, 'syncTemplates']);
            
            // Facebook
            Route::post('facebook', [FacebookController::class, 'create']);
            Route::patch('facebook/{inbox}', [FacebookController::class, 'update']);
            Route::get('facebook/pages', [FacebookController::class, 'pages']);
            
            // Telegram
            Route::post('telegram', [TelegramController::class, 'create']);
            Route::patch('telegram/{inbox}', [TelegramController::class, 'update']);
            Route::post('telegram/bot_info', [TelegramController::class, 'getBotInfo']);
            
            // Twitter
            Route::post('twitter', [TwitterController::class, 'create']);
            Route::patch('twitter/{inbox}', [TwitterController::class, 'update']);
            Route::get('twitter/authorize', [TwitterController::class, 'authorize']);
            Route::post('twitter/callback', [TwitterController::class, 'callback']);
            
            // Email
            Route::post('email', [EmailController::class, 'create']);
            Route::patch('email/{inbox}', [EmailController::class, 'update']);
            Route::post('email/test_imap', [EmailController::class, 'testImap']);
            Route::post('email/test_smtp', [EmailController::class, 'testSmtp']);
            
            // SMS
            Route::post('sms', [SmsController::class, 'create']);
            Route::patch('sms/{inbox}', [SmsController::class, 'update']);
            Route::get('sms/available_numbers', [SmsController::class, 'availableNumbers']);
            
            // Line
            Route::post('line', [LineController::class, 'create']);
            Route::patch('line/{inbox}', [LineController::class, 'update']);
            
            // Web Widget
            Route::post('web_widget', [WebWidgetController::class, 'create']);
            Route::patch('web_widget/{inbox}', [WebWidgetController::class, 'update']);
            Route::get('web_widget/{inbox}/script', [WebWidgetController::class, 'script']);
            
            // API Channel
            Route::post('api', [ChannelApiController::class, 'create']);
            Route::patch('api/{inbox}', [ChannelApiController::class, 'update']);
            Route::post('api/{inbox}/regenerate_key', [ChannelApiController::class, 'regenerateKey']);
            
            // Instagram
            Route::post('instagram', [InstagramController::class, 'create']);
            Route::patch('instagram/{inbox}', [InstagramController::class, 'update']);
            Route::get('instagram/authorize', [InstagramController::class, 'authorize']);
            Route::post('instagram/callback', [InstagramController::class, 'callback']);
            
            // Voice (Twilio)
            Route::post('voice', [VoiceController::class, 'create']);
            Route::patch('voice/{inbox}', [VoiceController::class, 'update']);
        });

        // Integrations
        Route::prefix('integrations')->group(function () {
            Route::get('/', [IntegrationsController::class, 'index']);
            Route::get('apps', [IntegrationsController::class, 'index']); // Alias
            Route::post('apps', [IntegrationsController::class, 'createApp']);
            Route::get('hooks', [IntegrationsController::class, 'hooks']);
            Route::post('hooks', [IntegrationsController::class, 'createHook']);
            Route::patch('hooks/{hook}', [IntegrationsController::class, 'updateHook']);
            Route::delete('hooks/{hook}', [IntegrationsController::class, 'deleteHook']);
            
            // Slack
            Route::get('slack', [SlackController::class, 'show']);
            Route::get('slack/authorize', [SlackController::class, 'authorize']);
            Route::post('slack', [SlackController::class, 'create']);
            Route::patch('slack', [SlackController::class, 'update']);
            Route::delete('slack', [SlackController::class, 'destroy']);
            Route::get('slack/channels', [SlackController::class, 'channels']);
            
            // Dialogflow
            Route::get('dialogflow', [DialogflowController::class, 'show']);
            Route::post('dialogflow', [DialogflowController::class, 'create']);
            Route::patch('dialogflow', [DialogflowController::class, 'update']);
            Route::delete('dialogflow', [DialogflowController::class, 'destroy']);
            Route::post('dialogflow/test', [DialogflowController::class, 'test']);
            
            // Linear
            Route::get('linear', [LinearController::class, 'show']);
            Route::post('linear', [LinearController::class, 'create']);
            Route::patch('linear', [LinearController::class, 'update']);
            Route::delete('linear', [LinearController::class, 'destroy']);
            Route::get('linear/teams', [LinearController::class, 'teams']);
            Route::get('linear/projects', [LinearController::class, 'projects']);
            Route::post('linear/issues', [LinearController::class, 'createIssue']);
            Route::post('linear/issues/link', [LinearController::class, 'linkIssue']);
            Route::post('linear/issues/unlink', [LinearController::class, 'unlinkIssue']);
            
            // Shopify
            Route::get('shopify', [ShopifyController::class, 'show']);
            Route::post('shopify', [ShopifyController::class, 'create']);
            Route::patch('shopify', [ShopifyController::class, 'update']);
            Route::delete('shopify', [ShopifyController::class, 'destroy']);
            Route::get('shopify/contacts/{contact}/customer', [ShopifyController::class, 'customer']);
            Route::get('shopify/contacts/{contact}/orders', [ShopifyController::class, 'orders']);
            Route::get('shopify/orders/{order}', [ShopifyController::class, 'order']);
            
            // OpenAI
            Route::get('openai', [OpenAIController::class, 'show']);
            Route::post('openai', [OpenAIController::class, 'create']);
            Route::patch('openai', [OpenAIController::class, 'update']);
            Route::delete('openai', [OpenAIController::class, 'destroy']);
            Route::post('openai/suggest', [OpenAIController::class, 'suggest']);
            Route::post('openai/summarize', [OpenAIController::class, 'summarize']);
            Route::post('openai/improve_tone', [OpenAIController::class, 'improveTone']);
        });

        // Companies
        Route::apiResource('companies', CompaniesController::class);
        Route::get('companies/search', [CompaniesController::class, 'search']);

        // Custom Roles
        Route::apiResource('custom_roles', CustomRolesController::class);

        // Assignment Policies V2
        Route::apiResource('assignment_policies', AssignmentPoliciesController::class);
        Route::get('assignment_policies/{assignment_policy}/inboxes', [AssignmentPoliciesController::class, 'inboxes']);
        Route::post('assignment_policies/{assignment_policy}/inboxes', [AssignmentPoliciesController::class, 'addInbox']);
        Route::delete('assignment_policies/{assignment_policy}/inboxes', [AssignmentPoliciesController::class, 'removeInbox']);

        // Agent Capacity Policies
        Route::apiResource('agent_capacity_policies', AgentCapacityPoliciesController::class);
        Route::get('agent_capacity_policies/{agent_capacity_policy}/users', [AgentCapacityPoliciesController::class, 'users']);
        Route::post('agent_capacity_policies/{agent_capacity_policy}/users', [AgentCapacityPoliciesController::class, 'addUser']);
        Route::delete('agent_capacity_policies/{agent_capacity_policy}/users', [AgentCapacityPoliciesController::class, 'removeUser']);
        Route::post('agent_capacity_policies/{agent_capacity_policy}/inbox_limits', [AgentCapacityPoliciesController::class, 'addInboxLimit']);
        Route::patch('agent_capacity_policies/{agent_capacity_policy}/inbox_limits/{inbox_limit}', [AgentCapacityPoliciesController::class, 'updateInboxLimit']);
        Route::delete('agent_capacity_policies/{agent_capacity_policy}/inbox_limits/{inbox_limit}', [AgentCapacityPoliciesController::class, 'removeInboxLimit']);

        // Notification Settings
        Route::get('notification_settings', [NotificationSettingsController::class, 'show']);
        Route::patch('notification_settings', [NotificationSettingsController::class, 'update']);

        // SAML Settings
        Route::get('saml_settings', [SamlSettingsController::class, 'show']);
        Route::post('saml_settings', [SamlSettingsController::class, 'store']);
        Route::patch('saml_settings', [SamlSettingsController::class, 'update']);
        Route::delete('saml_settings', [SamlSettingsController::class, 'destroy']);

        // Callbacks (OAuth and webhooks for channels)
        Route::prefix('callbacks')->group(function () {
            Route::get('facebook/authorize', [FacebookController::class, 'authorize']);
            Route::get('facebook/pages', [FacebookController::class, 'pages']);
            Route::post('facebook/create', [FacebookController::class, 'createFromCallback']);
            Route::get('twitter/authorize', [TwitterController::class, 'authorize']);
            Route::post('twitter/callback', [TwitterController::class, 'callback']);
        });
    });

    // Super Admin routes
    Route::prefix('super_admin')->middleware(EnsureSuperAdmin::class)->group(function () {
        // Instance Status
        Route::get('instance_status', [InstanceStatusController::class, 'show']);

        // Accounts
        Route::apiResource('accounts', SuperAdminAccountsController::class);
        Route::post('accounts/{account}/seed', [SuperAdminAccountsController::class, 'seed']);
        Route::post('accounts/{account}/reset_cache', [SuperAdminAccountsController::class, 'resetCache']);

        // Users
        Route::apiResource('users', SuperAdminUsersController::class);
        Route::delete('users/{user}/avatar', [SuperAdminUsersController::class, 'destroyAvatar']);

        // Agent Bots (Global)
        Route::apiResource('agent_bots', SuperAdminAgentBotsController::class);
        Route::delete('agent_bots/{agentBot}/avatar', [SuperAdminAgentBotsController::class, 'destroyAvatar']);

        // Platform Apps
        Route::apiResource('platform_apps', PlatformAppsController::class);
        Route::post('platform_apps/{platformApp}/regenerate_token', [PlatformAppsController::class, 'regenerateToken']);

        // Installation Configs
        Route::get('installation_configs', [InstallationConfigsController::class, 'index']);
        Route::post('installation_configs', [InstallationConfigsController::class, 'store']);
        Route::get('installation_configs/groups', [InstallationConfigsController::class, 'groups']);
        Route::get('installation_configs/group/{group}', [InstallationConfigsController::class, 'showByGroup']);
        Route::get('installation_configs/{installationConfig}', [InstallationConfigsController::class, 'show']);
        Route::patch('installation_configs/{installationConfig}', [InstallationConfigsController::class, 'update']);
        Route::delete('installation_configs/{installationConfig}', [InstallationConfigsController::class, 'destroy']);

        // Access Tokens
        Route::get('access_tokens', [SuperAdminAccessTokensController::class, 'index']);
        Route::post('access_tokens', [SuperAdminAccessTokensController::class, 'store']);
        Route::get('access_tokens/{accessToken}', [SuperAdminAccessTokensController::class, 'show']);
        Route::delete('access_tokens/{accessToken}', [SuperAdminAccessTokensController::class, 'destroy']);
        Route::delete('users/{user}/access_tokens', [SuperAdminAccessTokensController::class, 'revokeAllForUser']);
    });
});
