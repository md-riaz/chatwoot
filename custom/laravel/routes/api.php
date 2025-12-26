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
use App\Http\Middleware\EnsureSuperAdmin;
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
    });

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

    // Account-scoped resources
    Route::prefix('accounts/{account}')->group(function () {
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
        Route::post('conversations/{conversation}/toggle_typing_status', [ConversationsController::class, 'toggleTypingStatus']);
        Route::post('conversations/{conversation}/update_last_seen', [ConversationsController::class, 'updateLastSeen']);
        Route::post('conversations/{conversation}/unread', [ConversationsController::class, 'unread']);
        Route::post('conversations/{conversation}/custom_attributes', [ConversationsController::class, 'customAttributes']);
        Route::get('conversations/{conversation}/attachments', [ConversationsController::class, 'attachments']);

        // Messages (nested under conversations)
        Route::apiResource('conversations/{conversation}/messages', MessagesController::class);
        
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

        // Bulk Actions
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
        });

        // Integrations
        Route::prefix('integrations')->group(function () {
            Route::get('/', [IntegrationsController::class, 'index']);
            Route::get('hooks', [IntegrationsController::class, 'hooks']);
            Route::post('hooks', [IntegrationsController::class, 'createHook']);
            Route::patch('hooks/{hook}', [IntegrationsController::class, 'updateHook']);
            Route::delete('hooks/{hook}', [IntegrationsController::class, 'deleteHook']);
            
            // Slack
            Route::get('slack', [SlackController::class, 'show']);
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
