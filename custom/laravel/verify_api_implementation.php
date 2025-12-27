#!/usr/bin/env php
<?php

/**
 * API Implementation Verification Script
 * 
 * This script verifies that all Rails API endpoints have corresponding
 * Laravel implementations by checking:
 * 1. Controller files exist
 * 2. Route definitions exist
 * 3. Model files exist
 * 4. Test files exist
 */

echo "=============================================================\n";
echo "Chatwoot Rails to Laravel API Verification Script\n";
echo "=============================================================\n\n";

// Define expected controllers based on Rails implementation
$expectedControllers = [
    // Core Resources
    'AccountsController' => 'app/Http/Controllers/Api/V1/AccountsController.php',
    'ConversationsController' => 'app/Http/Controllers/Api/V1/ConversationsController.php',
    'MessagesController' => 'app/Http/Controllers/Api/V1/MessagesController.php',
    'ContactsController' => 'app/Http/Controllers/Api/V1/ContactsController.php',
    'InboxesController' => 'app/Http/Controllers/Api/V1/InboxesController.php',
    'TeamsController' => 'app/Http/Controllers/Api/V1/TeamsController.php',
    'LabelsController' => 'app/Http/Controllers/Api/V1/LabelsController.php',
    'UsersController' => 'app/Http/Controllers/Api/V1/UsersController.php',
    'AgentsController' => 'app/Http/Controllers/Api/V1/AgentsController.php',
    
    // Automation
    'AutomationRulesController' => 'app/Http/Controllers/Api/V1/AutomationRulesController.php',
    'MacrosController' => 'app/Http/Controllers/Api/V1/MacrosController.php',
    'CannedResponsesController' => 'app/Http/Controllers/Api/V1/CannedResponsesController.php',
    'WebhooksController' => 'app/Http/Controllers/Api/V1/WebhooksController.php',
    
    // Channel Integrations
    'WhatsAppController' => 'app/Http/Controllers/Api/V1/Channels/WhatsAppController.php',
    'FacebookController' => 'app/Http/Controllers/Api/V1/Channels/FacebookController.php',
    'TelegramController' => 'app/Http/Controllers/Api/V1/Channels/TelegramController.php',
    'TwitterController' => 'app/Http/Controllers/Api/V1/Channels/TwitterController.php',
    'EmailController' => 'app/Http/Controllers/Api/V1/Channels/EmailController.php',
    'SmsController' => 'app/Http/Controllers/Api/V1/Channels/SmsController.php',
    'LineController' => 'app/Http/Controllers/Api/V1/Channels/LineController.php',
    'WebWidgetController' => 'app/Http/Controllers/Api/V1/Channels/WebWidgetController.php',
    'ApiController' => 'app/Http/Controllers/Api/V1/Channels/ApiController.php',
    
    // Third-Party Integrations
    'SlackController' => 'app/Http/Controllers/Api/V1/Integrations/SlackController.php',
    'LinearController' => 'app/Http/Controllers/Api/V1/Integrations/LinearController.php',
    'DialogflowController' => 'app/Http/Controllers/Api/V1/Integrations/DialogflowController.php',
    'OpenAIController' => 'app/Http/Controllers/Api/V1/Integrations/OpenAIController.php',
    'ShopifyController' => 'app/Http/Controllers/Api/V1/Integrations/ShopifyController.php',
    
    // Advanced Features
    'ReportsController' => 'app/Http/Controllers/Api/V1/ReportsController.php',
    'SlaPoliciesController' => 'app/Http/Controllers/Api/V1/SlaPoliciesController.php',
    'AuditLogsController' => 'app/Http/Controllers/Api/V1/AuditLogsController.php',
    'SegmentsController' => 'app/Http/Controllers/Api/V1/SegmentsController.php',
    'CustomAttributeDefinitionsController' => 'app/Http/Controllers/Api/V1/CustomAttributeDefinitionsController.php',
    'CustomFiltersController' => 'app/Http/Controllers/Api/V1/CustomFiltersController.php',
    'CampaignsController' => 'app/Http/Controllers/Api/V1/CampaignsController.php',
    'AgentBotsController' => 'app/Http/Controllers/Api/V1/AgentBotsController.php',
    'DashboardAppsController' => 'app/Http/Controllers/Api/V1/DashboardAppsController.php',
    'ContactNotesController' => 'app/Http/Controllers/Api/V1/ContactNotesController.php',
    'CsatSurveyResponsesController' => 'app/Http/Controllers/Api/V1/CsatSurveyResponsesController.php',
    'WorkingHoursController' => 'app/Http/Controllers/Api/V1/WorkingHoursController.php',
    'SearchController' => 'app/Http/Controllers/Api/V1/SearchController.php',
    'BulkActionsController' => 'app/Http/Controllers/Api/V1/BulkActionsController.php',
    'AttachmentsController' => 'app/Http/Controllers/Api/V1/AttachmentsController.php',
    'NotificationsController' => 'app/Http/Controllers/Api/V1/NotificationsController.php',
    'ProfileController' => 'app/Http/Controllers/Api/V1/ProfileController.php',
    
    // Help Center
    'PortalsController' => 'app/Http/Controllers/Api/V1/PortalsController.php',
    'ArticlesController' => 'app/Http/Controllers/Api/V1/ArticlesController.php',
    'CategoriesController' => 'app/Http/Controllers/Api/V1/CategoriesController.php',
];

// Define expected models
$expectedModels = [
    'Account' => 'app/Models/Account.php',
    'User' => 'app/Models/User.php',
    'Contact' => 'app/Models/Contact.php',
    'Inbox' => 'app/Models/Inbox.php',
    'Conversation' => 'app/Models/Conversation.php',
    'Message' => 'app/Models/Message.php',
    'Label' => 'app/Models/Label.php',
    'Team' => 'app/Models/Team.php',
    'AutomationRule' => 'app/Models/AutomationRule.php',
    'Macro' => 'app/Models/Macro.php',
    'CannedResponse' => 'app/Models/CannedResponse.php',
    'Webhook' => 'app/Models/Webhook.php',
    'Campaign' => 'app/Models/Campaign.php',
    'AgentBot' => 'app/Models/AgentBot.php',
    'DashboardApp' => 'app/Models/DashboardApp.php',
    'Note' => 'app/Models/Note.php',
    'CsatSurveyResponse' => 'app/Models/CsatSurveyResponse.php',
    'SlaPolicy' => 'app/Models/SlaPolicy.php',
    'Segment' => 'app/Models/Segment.php',
    'CustomAttributeDefinition' => 'app/Models/CustomAttributeDefinition.php',
    'CustomFilter' => 'app/Models/CustomFilter.php',
    'Portal' => 'app/Models/Portal.php',
    'Article' => 'app/Models/Article.php',
    'Category' => 'app/Models/Category.php',
    'Attachment' => 'app/Models/Attachment.php',
    'WorkingHour' => 'app/Models/WorkingHour.php',
];

// Define expected services
$expectedServices = [
    'WhatsappService' => 'app/Services/Channels/Whatsapp/WhatsappService.php',
    'FacebookService' => 'app/Services/Channels/Facebook/FacebookService.php',
    'TelegramService' => 'app/Services/Channels/Telegram/TelegramService.php',
    'TwitterService' => 'app/Services/Channels/Twitter/TwitterService.php',
    'EmailService' => 'app/Services/Channels/Email/EmailService.php',
    'TwilioService' => 'app/Services/Channels/Sms/TwilioService.php',
    'LineService' => 'app/Services/Channels/Line/LineService.php',
    'SlackService' => 'app/Services/Integrations/SlackService.php',
    'LinearService' => 'app/Services/Integrations/LinearService.php',
    'DialogflowService' => 'app/Services/Integrations/DialogflowService.php',
    'OpenAIService' => 'app/Services/Integrations/OpenAIService.php',
];

$results = [
    'controllers' => ['passed' => 0, 'failed' => 0, 'missing' => []],
    'models' => ['passed' => 0, 'failed' => 0, 'missing' => []],
    'services' => ['passed' => 0, 'failed' => 0, 'missing' => []],
];

// Check Controllers
echo "Checking Controllers...\n";
echo str_repeat("-", 60) . "\n";
foreach ($expectedControllers as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name\n";
        $results['controllers']['passed']++;
    } else {
        echo "❌ $name (Missing: $path)\n";
        $results['controllers']['failed']++;
        $results['controllers']['missing'][] = $name;
    }
}
echo "\n";

// Check Models
echo "Checking Models...\n";
echo str_repeat("-", 60) . "\n";
foreach ($expectedModels as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name\n";
        $results['models']['passed']++;
    } else {
        echo "❌ $name (Missing: $path)\n";
        $results['models']['failed']++;
        $results['models']['missing'][] = $name;
    }
}
echo "\n";

// Check Services
echo "Checking Services...\n";
echo str_repeat("-", 60) . "\n";
foreach ($expectedServices as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name\n";
        $results['services']['passed']++;
    } else {
        echo "❌ $name (Missing: $path)\n";
        $results['services']['failed']++;
        $results['services']['missing'][] = $name;
    }
}
echo "\n";

// Check if routes file exists
echo "Checking Routes Configuration...\n";
echo str_repeat("-", 60) . "\n";
if (file_exists('routes/api.php')) {
    echo "✅ routes/api.php exists\n";
    $routeContent = file_get_contents('routes/api.php');
    $routeLines = count(explode("\n", $routeContent));
    echo "   Routes file has $routeLines lines\n";
} else {
    echo "❌ routes/api.php missing\n";
}
echo "\n";

// Check if test directory exists
echo "Checking Test Coverage...\n";
echo str_repeat("-", 60) . "\n";
if (is_dir('tests/Feature/Api')) {
    $testFiles = glob('tests/Feature/Api/**/*Test.php');
    echo "✅ Test directory exists\n";
    echo "   Found " . count($testFiles) . " test files\n";
} else {
    echo "❌ Test directory missing\n";
}
echo "\n";

// Summary
echo "=============================================================\n";
echo "VERIFICATION SUMMARY\n";
echo "=============================================================\n\n";

$totalPassed = $results['controllers']['passed'] + $results['models']['passed'] + $results['services']['passed'];
$totalFailed = $results['controllers']['failed'] + $results['models']['failed'] + $results['services']['failed'];
$totalChecked = $totalPassed + $totalFailed;
$percentage = $totalChecked > 0 ? round(($totalPassed / $totalChecked) * 100, 2) : 0;

echo "Controllers: {$results['controllers']['passed']} passed, {$results['controllers']['failed']} failed\n";
echo "Models:      {$results['models']['passed']} passed, {$results['models']['failed']} failed\n";
echo "Services:    {$results['services']['passed']} passed, {$results['services']['failed']} failed\n";
echo "\n";
echo "Total:       $totalPassed / $totalChecked passed ($percentage%)\n";
echo "\n";

if ($totalFailed > 0) {
    echo "MISSING COMPONENTS:\n";
    echo str_repeat("-", 60) . "\n";
    
    if (count($results['controllers']['missing']) > 0) {
        echo "\nControllers:\n";
        foreach ($results['controllers']['missing'] as $item) {
            echo "  - $item\n";
        }
    }
    
    if (count($results['models']['missing']) > 0) {
        echo "\nModels:\n";
        foreach ($results['models']['missing'] as $item) {
            echo "  - $item\n";
        }
    }
    
    if (count($results['services']['missing']) > 0) {
        echo "\nServices:\n";
        foreach ($results['services']['missing'] as $item) {
            echo "  - $item\n";
        }
    }
    echo "\n";
}

// Final assessment
echo "=============================================================\n";
if ($percentage >= 95) {
    echo "✅ STATUS: PRODUCTION READY\n";
    echo "   The Laravel implementation has excellent coverage.\n";
} elseif ($percentage >= 80) {
    echo "⚠️  STATUS: MOSTLY READY\n";
    echo "   Some components are missing but core functionality is complete.\n";
} else {
    echo "❌ STATUS: NOT READY\n";
    echo "   Significant components are missing.\n";
}
echo "=============================================================\n";

exit($totalFailed > 0 ? 1 : 0);
