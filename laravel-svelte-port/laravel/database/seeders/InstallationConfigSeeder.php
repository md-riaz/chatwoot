<?php

namespace Database\Seeders;

use App\Enums\Feature;
use App\Models\InstallationConfig;
use Illuminate\Database\Seeder;

class InstallationConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Loading installation configuration...');

        // Load feature defaults
        $enabledFeatures = Feature::getEnabledByDefault();
        
        InstallationConfig::updateOrCreate(
            ['name' => 'ACCOUNT_LEVEL_FEATURE_DEFAULTS'],
            [
                'display_title' => 'Account Level Feature Defaults',
                'description' => 'Default features enabled for new accounts',
                'type' => 'array',
                'locked' => true,
                'serialized_value' => $enabledFeatures,
            ]
        );

        // Load all Rails-compatible configuration settings
        $this->seedRailsCompatibleConfigs();

        $this->command->info('Configuration loading completed:');
        $this->command->line("  Features loaded: " . count($enabledFeatures));
        $this->command->line("  Total configs: " . InstallationConfig::count());

        $this->command->info('Installation configuration seeded successfully!');
    }

    /**
     * Seed Rails-compatible configuration settings.
     */
    private function seedRailsCompatibleConfigs(): void
    {
        $configs = [
            // General Settings
            [
                'name' => 'ENABLE_ACCOUNT_SIGNUP',
                'display_title' => 'Enable Account Signup',
                'description' => 'Allow users to signup for new accounts',
                'value' => false,
                'type' => 'boolean',
                'locked' => false,
            ],
            [
                'name' => 'FIREBASE_PROJECT_ID',
                'display_title' => 'Firebase Project ID',
                'description' => 'Firebase project ID',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'FIREBASE_CREDENTIALS',
                'display_title' => 'Firebase Credentials',
                'description' => 'Contents of your Firebase credentials json file',
                'value' => '',
                'type' => 'code',
                'locked' => false,
            ],
            [
                'name' => 'WEBHOOK_TIMEOUT',
                'display_title' => 'Webhook request timeout (seconds)',
                'description' => 'Maximum time Chatwoot waits for a webhook response before failing the request',
                'value' => 5,
                'type' => 'integer',
                'locked' => false,
            ],
            [
                'name' => 'MAXIMUM_FILE_UPLOAD_SIZE',
                'display_title' => 'Attachment size limit (MB)',
                'description' => 'Maximum attachment size in MB allowed for uploads',
                'value' => 40,
                'type' => 'integer',
                'locked' => false,
            ],

            // Email Settings
            [
                'name' => 'MAILER_INBOUND_EMAIL_DOMAIN',
                'display_title' => 'Inbound Email Domain',
                'description' => 'The domain name to be used for generating conversation continuity emails (reply+id@domain.com)',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'MAILER_SUPPORT_EMAIL',
                'display_title' => 'Support Email',
                'description' => 'The support email address for your installation',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],

            // Messenger Settings
            [
                'name' => 'FB_APP_ID',
                'display_title' => 'Facebook App ID',
                'description' => 'Facebook App ID for Messenger integration',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'FB_VERIFY_TOKEN',
                'display_title' => 'Facebook Verify Token',
                'description' => 'Facebook webhook verification token',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
            [
                'name' => 'FB_APP_SECRET',
                'display_title' => 'Facebook App Secret',
                'description' => 'Facebook App Secret for Messenger integration',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
            [
                'name' => 'FACEBOOK_API_VERSION',
                'display_title' => 'Facebook API Version',
                'description' => 'Facebook Graph API version to use',
                'value' => 'v18.0',
                'type' => 'select',
                'locked' => false,
            ],
            [
                'name' => 'ENABLE_MESSENGER_CHANNEL_HUMAN_AGENT',
                'display_title' => 'Enable Messenger Human Agent',
                'description' => 'Allow human agents to take over Messenger conversations',
                'value' => true,
                'type' => 'boolean',
                'locked' => false,
            ],

            // Instagram Settings
            [
                'name' => 'INSTAGRAM_APP_ID',
                'display_title' => 'Instagram App ID',
                'description' => 'Instagram App ID for integration',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'INSTAGRAM_APP_SECRET',
                'display_title' => 'Instagram App Secret',
                'description' => 'Instagram App Secret for integration',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
            [
                'name' => 'INSTAGRAM_VERIFY_TOKEN',
                'display_title' => 'Instagram Verify Token',
                'description' => 'Instagram webhook verification token',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
            [
                'name' => 'INSTAGRAM_API_VERSION',
                'display_title' => 'Instagram API Version',
                'description' => 'Instagram Graph API version to use',
                'value' => 'v22.0',
                'type' => 'select',
                'locked' => true,
            ],
            [
                'name' => 'ENABLE_INSTAGRAM_CHANNEL_HUMAN_AGENT',
                'display_title' => 'Enable Instagram Human Agent',
                'description' => 'Allow human agents to take over Instagram conversations',
                'value' => true,
                'type' => 'boolean',
                'locked' => false,
            ],

            // TikTok Settings
            [
                'name' => 'TIKTOK_APP_ID',
                'display_title' => 'TikTok App ID',
                'description' => 'TikTok App ID for integration',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'TIKTOK_APP_SECRET',
                'display_title' => 'TikTok App Secret',
                'description' => 'TikTok App Secret for integration',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],

            // Google Settings
            [
                'name' => 'GOOGLE_OAUTH_CLIENT_ID',
                'display_title' => 'Google OAuth Client ID',
                'description' => 'Google OAuth Client ID',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'GOOGLE_OAUTH_CLIENT_SECRET',
                'display_title' => 'Google OAuth Client Secret',
                'description' => 'Google OAuth Client Secret',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
            [
                'name' => 'GOOGLE_OAUTH_REDIRECT_URI',
                'display_title' => 'Google OAuth Redirect URI',
                'description' => 'Google OAuth Redirect URI',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'ENABLE_GOOGLE_OAUTH_LOGIN',
                'display_title' => 'Enable Google OAuth Login',
                'description' => 'Allow users to login with Google OAuth',
                'value' => true,
                'type' => 'boolean',
                'locked' => false,
            ],

            // Microsoft Settings
            [
                'name' => 'AZURE_APP_ID',
                'display_title' => 'Azure App ID',
                'description' => 'Microsoft Azure App ID',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'AZURE_APP_SECRET',
                'display_title' => 'Azure App Secret',
                'description' => 'Microsoft Azure App Secret',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],

            // Linear Settings
            [
                'name' => 'LINEAR_CLIENT_ID',
                'display_title' => 'Linear Client ID',
                'description' => 'Linear OAuth Client ID',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'LINEAR_CLIENT_SECRET',
                'display_title' => 'Linear Client Secret',
                'description' => 'Linear OAuth Client Secret',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],

            // Notion Settings
            [
                'name' => 'NOTION_CLIENT_ID',
                'display_title' => 'Notion Client ID',
                'description' => 'Notion OAuth Client ID',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'NOTION_CLIENT_SECRET',
                'display_title' => 'Notion Client Secret',
                'description' => 'Notion OAuth Client Secret',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
            [
                'name' => 'NOTION_VERSION',
                'display_title' => 'Notion Version',
                'description' => 'Notion API version',
                'value' => '2022-06-28',
                'type' => 'text',
                'locked' => false,
            ],

            // Slack Settings
            [
                'name' => 'SLACK_CLIENT_ID',
                'display_title' => 'Slack Client ID',
                'description' => 'Slack OAuth Client ID',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'SLACK_CLIENT_SECRET',
                'display_title' => 'Slack Client Secret',
                'description' => 'Slack OAuth Client Secret',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],

            // WhatsApp Embedded Settings
            [
                'name' => 'WHATSAPP_APP_ID',
                'display_title' => 'WhatsApp App ID',
                'description' => 'WhatsApp Business App ID',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'WHATSAPP_APP_SECRET',
                'display_title' => 'WhatsApp App Secret',
                'description' => 'WhatsApp Business App Secret',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
            [
                'name' => 'WHATSAPP_CONFIGURATION_ID',
                'display_title' => 'WhatsApp Configuration ID',
                'description' => 'WhatsApp Business Configuration ID',
                'value' => '',
                'type' => 'text',
                'locked' => false,
            ],
            [
                'name' => 'WHATSAPP_API_VERSION',
                'display_title' => 'WhatsApp API Version',
                'description' => 'WhatsApp Business API version to use',
                'value' => 'v22.0',
                'type' => 'select',
                'locked' => false,
            ],

            // Shopify Settings
            [
                'name' => 'SHOPIFY_CLIENT_ID',
                'display_title' => 'Shopify Client ID',
                'description' => 'Shopify App Client ID',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
            [
                'name' => 'SHOPIFY_CLIENT_SECRET',
                'display_title' => 'Shopify Client Secret',
                'description' => 'Shopify App Client Secret',
                'value' => '',
                'type' => 'secret',
                'locked' => false,
            ],
        ];

        foreach ($configs as $config) {
            InstallationConfig::updateOrCreate(
                ['name' => $config['name']],
                [
                    'serialized_value' => ['value' => $config['value']],
                    'display_title' => $config['display_title'],
                    'description' => $config['description'],
                    'type' => $config['type'],
                    'locked' => $config['locked']
                ]
            );
        }

        $this->command->info('Seeded ' . count($configs) . ' Rails-compatible configuration settings');
    }
}