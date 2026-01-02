# Rails to Laravel Migration Guide

This comprehensive guide covers migrating from Chatwoot Rails to ClearLine Laravel, ensuring data integrity and functional parity throughout the migration process.

## Table of Contents

1. [Migration Overview](#migration-overview)
2. [Pre-Migration Assessment](#pre-migration-assessment)
3. [Data Migration Strategy](#data-migration-strategy)
4. [Step-by-Step Migration Process](#step-by-step-migration-process)
5. [Feature Mapping](#feature-mapping)
6. [API Endpoint Mapping](#api-endpoint-mapping)
7. [Configuration Migration](#configuration-migration)
8. [Testing and Validation](#testing-and-validation)
9. [Rollback Procedures](#rollback-procedures)
10. [Post-Migration Tasks](#post-migration-tasks)

## Migration Overview

### What's Included in Migration

✅ **Complete Feature Parity**
- All core functionality (conversations, contacts, inboxes, teams)
- All channel integrations (WhatsApp, Facebook, Email, SMS, etc.)
- All third-party integrations (Slack, Linear, Shopify, OpenAI)
- Enterprise features (SLA policies, custom roles, SAML SSO)
- Super admin functionality
- Real-time features (WebSocket/ActionCable → Reverb)
- Background job processing (Sidekiq → Horizon)

✅ **Data Preservation**
- All user accounts and permissions
- Complete conversation history
- Contact information and custom attributes
- Channel configurations and integrations
- Automation rules and macros
- Reports and analytics data

❌ **Excluded Features**
- Captain AI module (by design decision)
- Legacy features marked for deprecation

### Migration Benefits

- **Performance**: Laravel 12 with modern PHP 8.2+ performance
- **Scalability**: Better queue management with Horizon
- **Real-time**: Laravel Reverb for WebSocket connections
- **Security**: Modern authentication with Sanctum
- **Maintainability**: Clean Laravel architecture and conventions

## Pre-Migration Assessment

### 1. Current System Audit

Before starting migration, audit your current Rails installation:

```bash
# Check Rails version and dependencies
cd /path/to/chatwoot
cat Gemfile.lock | grep rails
bundle list

# Check database size and structure
rails console
ActiveRecord::Base.connection.tables.count
# Check largest tables
ActiveRecord::Base.connection.execute("
  SELECT schemaname,tablename,attname,n_distinct,correlation 
  FROM pg_stats 
  WHERE schemaname = 'public' 
  ORDER BY n_distinct DESC LIMIT 10;
")

# Check data volumes
User.count
Account.count
Conversation.count
Message.count
Contact.count
```

### 2. Integration Inventory

Document all active integrations:

```bash
# Check configured channels
rails console
Channel::WebWidget.count
Channel::Email.count
Channel::Whatsapp.count
Channel::FacebookPage.count
# ... check all channel types

# Check integrations
Integrations::Slack.count
Integrations::Dialogflow.count
# ... check all integration types

# Check webhooks
Webhook.count
```

### 3. Custom Modifications

Document any custom modifications to the Rails codebase:
- Custom controllers or models
- Modified views or templates
- Custom integrations
- Configuration changes
- Database schema modifications

### 4. Performance Baseline

Establish performance baselines:
- Average response times
- Database query performance
- Queue processing times
- WebSocket connection counts
- Memory and CPU usage

## Data Migration Strategy

### Migration Approach

We recommend a **parallel migration** approach:

1. **Setup Laravel system** alongside existing Rails
2. **Migrate data** in phases while Rails remains operational
3. **Sync incremental changes** during migration
4. **Switch traffic** to Laravel system
5. **Decommission Rails** after validation

### Data Migration Phases

#### Phase 1: Core Data (Accounts, Users, Contacts)
- User accounts and authentication data
- Account configurations
- Contact information and custom attributes
- Team and agent assignments

#### Phase 2: Conversation Data
- Conversation records and metadata
- Message history and attachments
- Labels and custom attributes
- Conversation assignments and status

#### Phase 3: Configuration Data
- Inbox configurations and channels
- Automation rules and macros
- Canned responses and templates
- Webhook configurations

#### Phase 4: Integration Data
- Third-party integration settings
- Channel-specific configurations
- API keys and authentication tokens
- Integration-specific data

## Step-by-Step Migration Process

### Step 1: Environment Setup

#### 1.1 Setup Laravel Environment

```bash
# Clone and setup Laravel application
git clone https://github.com/your-org/clearline.git
cd clearline/custom/laravel

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env.migration
# Edit .env.migration with migration-specific settings
```

#### 1.2 Configure Migration Database

```bash
# Create migration database (separate from production)
createdb clearline_migration

# Configure Laravel to use migration database
# In .env.migration:
DB_DATABASE=clearline_migration
DB_HOST=your-rails-db-host
DB_PORT=5432
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```

#### 1.3 Setup Migration Tools

```bash
# Install migration dependencies
composer require doctrine/dbal
composer require maatwebsite/excel  # For data export/import

# Create migration commands
php artisan make:command MigrateFromRails
```

### Step 2: Schema Migration

#### 2.1 Run Laravel Migrations

```bash
# Run all Laravel migrations
php artisan migrate --env=migration

# Verify schema
php artisan tinker
Schema::getTableListing()
```

#### 2.2 Schema Comparison

Create a schema comparison script:

```php
<?php
// database/migrations/compare_schemas.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Compare table structures between Rails and Laravel
$railsTables = DB::connection('rails')->select("
    SELECT table_name 
    FROM information_schema.tables 
    WHERE table_schema = 'public'
");

$laravelTables = Schema::getTableListing();

// Compare and report differences
foreach ($railsTables as $table) {
    if (!in_array($table->table_name, $laravelTables)) {
        echo "Missing table: {$table->table_name}\n";
    }
}
```

### Step 3: Data Migration Scripts

#### 3.1 User and Account Migration

```php
<?php
// app/Console/Commands/MigrateUsers.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountUser;

class MigrateUsers extends Command
{
    protected $signature = 'migrate:users';
    protected $description = 'Migrate users and accounts from Rails';

    public function handle()
    {
        $this->info('Starting user migration...');

        // Migrate accounts first
        $railsAccounts = DB::connection('rails')
            ->table('accounts')
            ->orderBy('id')
            ->get();

        foreach ($railsAccounts as $railsAccount) {
            Account::create([
                'id' => $railsAccount->id,
                'name' => $railsAccount->name,
                'locale' => $railsAccount->locale ?? 'en',
                'domain' => $railsAccount->domain,
                'support_email' => $railsAccount->support_email,
                'settings' => json_decode($railsAccount->settings ?? '{}', true),
                'created_at' => $railsAccount->created_at,
                'updated_at' => $railsAccount->updated_at,
            ]);
        }

        $this->info('Migrated ' . $railsAccounts->count() . ' accounts');

        // Migrate users
        $railsUsers = DB::connection('rails')
            ->table('users')
            ->orderBy('id')
            ->get();

        foreach ($railsUsers as $railsUser) {
            User::create([
                'id' => $railsUser->id,
                'name' => $railsUser->name,
                'email' => $railsUser->email,
                'password' => $railsUser->encrypted_password, // Keep existing hash
                'type' => $railsUser->type ?? 'user',
                'availability_status' => $railsUser->availability_status ?? 'online',
                'auto_offline' => $railsUser->auto_offline ?? true,
                'confirmed' => $railsUser->confirmed_at !== null,
                'email_verified_at' => $railsUser->confirmed_at,
                'created_at' => $railsUser->created_at,
                'updated_at' => $railsUser->updated_at,
            ]);
        }

        $this->info('Migrated ' . $railsUsers->count() . ' users');

        // Migrate account-user relationships
        $railsAccountUsers = DB::connection('rails')
            ->table('account_users')
            ->get();

        foreach ($railsAccountUsers as $railsAccountUser) {
            AccountUser::create([
                'account_id' => $railsAccountUser->account_id,
                'user_id' => $railsAccountUser->user_id,
                'role' => $railsAccountUser->role,
                'inviter_id' => $railsAccountUser->inviter_id,
                'created_at' => $railsAccountUser->created_at,
                'updated_at' => $railsAccountUser->updated_at,
            ]);
        }

        $this->info('Migrated ' . $railsAccountUsers->count() . ' account-user relationships');
        $this->info('User migration completed!');
    }
}
```

#### 3.2 Contact Migration

```php
<?php
// app/Console/Commands/MigrateContacts.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;

class MigrateContacts extends Command
{
    protected $signature = 'migrate:contacts';
    protected $description = 'Migrate contacts from Rails';

    public function handle()
    {
        $this->info('Starting contact migration...');

        $railsContacts = DB::connection('rails')
            ->table('contacts')
            ->orderBy('id')
            ->chunk(1000, function ($contacts) {
                foreach ($contacts as $railsContact) {
                    Contact::create([
                        'id' => $railsContact->id,
                        'account_id' => $railsContact->account_id,
                        'name' => $railsContact->name,
                        'email' => $railsContact->email,
                        'phone_number' => $railsContact->phone_number,
                        'identifier' => $railsContact->identifier,
                        'custom_attributes' => json_decode($railsContact->custom_attributes ?? '{}', true),
                        'additional_attributes' => json_decode($railsContact->additional_attributes ?? '{}', true),
                        'last_activity_at' => $railsContact->last_activity_at,
                        'created_at' => $railsContact->created_at,
                        'updated_at' => $railsContact->updated_at,
                    ]);
                }
                $this->info('Processed ' . $contacts->count() . ' contacts...');
            });

        $this->info('Contact migration completed!');
    }
}
```

#### 3.3 Conversation and Message Migration

```php
<?php
// app/Console/Commands/MigrateConversations.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Conversation;
use App\Models\Message;

class MigrateConversations extends Command
{
    protected $signature = 'migrate:conversations {--batch-size=500}';
    protected $description = 'Migrate conversations and messages from Rails';

    public function handle()
    {
        $batchSize = $this->option('batch-size');
        $this->info("Starting conversation migration with batch size: {$batchSize}");

        // Migrate conversations
        DB::connection('rails')
            ->table('conversations')
            ->orderBy('id')
            ->chunk($batchSize, function ($conversations) {
                foreach ($conversations as $railsConversation) {
                    Conversation::create([
                        'id' => $railsConversation->id,
                        'account_id' => $railsConversation->account_id,
                        'inbox_id' => $railsConversation->inbox_id,
                        'contact_id' => $railsConversation->contact_id,
                        'assignee_id' => $railsConversation->assignee_id,
                        'team_id' => $railsConversation->team_id,
                        'status' => $railsConversation->status,
                        'priority' => $railsConversation->priority,
                        'display_id' => $railsConversation->display_id,
                        'locked' => $railsConversation->locked ?? false,
                        'custom_attributes' => json_decode($railsConversation->custom_attributes ?? '{}', true),
                        'snoozed_until' => $railsConversation->snoozed_until,
                        'first_reply_created_at' => $railsConversation->first_reply_created_at,
                        'last_activity_at' => $railsConversation->last_activity_at,
                        'created_at' => $railsConversation->created_at,
                        'updated_at' => $railsConversation->updated_at,
                    ]);
                }
                $this->info('Processed ' . $conversations->count() . ' conversations...');
            });

        // Migrate messages
        $this->info('Starting message migration...');
        
        DB::connection('rails')
            ->table('messages')
            ->orderBy('id')
            ->chunk($batchSize, function ($messages) {
                foreach ($messages as $railsMessage) {
                    Message::create([
                        'id' => $railsMessage->id,
                        'content' => $railsMessage->content,
                        'content_type' => $railsMessage->content_type ?? 'text',
                        'content_attributes' => json_decode($railsMessage->content_attributes ?? '{}', true),
                        'message_type' => $railsMessage->message_type,
                        'private' => $railsMessage->private ?? false,
                        'sender_type' => $railsMessage->sender_type,
                        'sender_id' => $railsMessage->sender_id,
                        'conversation_id' => $railsMessage->conversation_id,
                        'inbox_id' => $railsMessage->inbox_id,
                        'source_id' => $railsMessage->source_id,
                        'external_source_ids' => json_decode($railsMessage->external_source_ids ?? '{}', true),
                        'additional_attributes' => json_decode($railsMessage->additional_attributes ?? '{}', true),
                        'processed_message_content' => $railsMessage->processed_message_content,
                        'sentiment' => json_decode($railsMessage->sentiment ?? '{}', true),
                        'created_at' => $railsMessage->created_at,
                        'updated_at' => $railsMessage->updated_at,
                    ]);
                }
                $this->info('Processed ' . $messages->count() . ' messages...');
            });

        $this->info('Conversation and message migration completed!');
    }
}
```

#### 3.4 Channel and Integration Migration

```php
<?php
// app/Console/Commands/MigrateChannels.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Inbox;

class MigrateChannels extends Command
{
    protected $signature = 'migrate:channels';
    protected $description = 'Migrate inboxes and channels from Rails';

    public function handle()
    {
        $this->info('Starting channel migration...');

        // Migrate inboxes
        $railsInboxes = DB::connection('rails')
            ->table('inboxes')
            ->orderBy('id')
            ->get();

        foreach ($railsInboxes as $railsInbox) {
            Inbox::create([
                'id' => $railsInbox->id,
                'account_id' => $railsInbox->account_id,
                'name' => $railsInbox->name,
                'channel_id' => $railsInbox->channel_id,
                'channel_type' => $railsInbox->channel_type,
                'enable_auto_assignment' => $railsInbox->enable_auto_assignment ?? true,
                'enable_email_collect' => $railsInbox->enable_email_collect ?? true,
                'greeting_enabled' => $railsInbox->greeting_enabled ?? false,
                'greeting_message' => $railsInbox->greeting_message,
                'working_hours_enabled' => $railsInbox->working_hours_enabled ?? false,
                'out_of_office_message' => $railsInbox->out_of_office_message,
                'timezone' => $railsInbox->timezone ?? 'UTC',
                'csat_survey_enabled' => $railsInbox->csat_survey_enabled ?? false,
                'allow_messages_after_resolved' => $railsInbox->allow_messages_after_resolved ?? true,
                'lock_to_single_conversation' => $railsInbox->lock_to_single_conversation ?? false,
                'portal_id' => $railsInbox->portal_id,
                'created_at' => $railsInbox->created_at,
                'updated_at' => $railsInbox->updated_at,
            ]);
        }

        $this->info('Migrated ' . $railsInboxes->count() . ' inboxes');

        // Migrate specific channel types
        $this->migrateWebWidgetChannels();
        $this->migrateEmailChannels();
        $this->migrateWhatsAppChannels();
        // ... migrate other channel types

        $this->info('Channel migration completed!');
    }

    private function migrateWebWidgetChannels()
    {
        $channels = DB::connection('rails')
            ->table('channel_web_widgets')
            ->get();

        foreach ($channels as $channel) {
            DB::table('channel_web_widgets')->insert([
                'id' => $channel->id,
                'website_name' => $channel->website_name,
                'website_url' => $channel->website_url,
                'website_token' => $channel->website_token,
                'widget_color' => $channel->widget_color,
                'welcome_title' => $channel->welcome_title,
                'welcome_tagline' => $channel->welcome_tagline,
                'feature_flags' => json_decode($channel->feature_flags ?? '{}', true),
                'reply_time' => $channel->reply_time,
                'hmac_mandatory' => $channel->hmac_mandatory ?? false,
                'hmac_token' => $channel->hmac_token,
                'pre_chat_form_enabled' => $channel->pre_chat_form_enabled ?? false,
                'pre_chat_form_options' => json_decode($channel->pre_chat_form_options ?? '{}', true),
                'continuity_via_email' => $channel->continuity_via_email ?? false,
                'created_at' => $channel->created_at,
                'updated_at' => $channel->updated_at,
            ]);
        }

        $this->info('Migrated ' . $channels->count() . ' web widget channels');
    }

    private function migrateEmailChannels()
    {
        $channels = DB::connection('rails')
            ->table('channel_email')
            ->get();

        foreach ($channels as $channel) {
            DB::table('channel_email')->insert([
                'id' => $channel->id,
                'email' => $channel->email,
                'forward_to_email' => $channel->forward_to_email,
                'imap_enabled' => $channel->imap_enabled ?? false,
                'imap_address' => $channel->imap_address,
                'imap_port' => $channel->imap_port,
                'imap_login' => $channel->imap_login,
                'imap_password' => $channel->imap_password,
                'imap_enable_ssl' => $channel->imap_enable_ssl ?? true,
                'smtp_enabled' => $channel->smtp_enabled ?? false,
                'smtp_address' => $channel->smtp_address,
                'smtp_port' => $channel->smtp_port,
                'smtp_login' => $channel->smtp_login,
                'smtp_password' => $channel->smtp_password,
                'smtp_domain' => $channel->smtp_domain,
                'smtp_enable_ssl_tls' => $channel->smtp_enable_ssl_tls ?? true,
                'smtp_enable_starttls_auto' => $channel->smtp_enable_starttls_auto ?? true,
                'smtp_openssl_verify_mode' => $channel->smtp_openssl_verify_mode ?? 'peer',
                'smtp_authentication' => $channel->smtp_authentication ?? 'login',
                'provider_config' => json_decode($channel->provider_config ?? '{}', true),
                'created_at' => $channel->created_at,
                'updated_at' => $channel->updated_at,
            ]);
        }

        $this->info('Migrated ' . $channels->count() . ' email channels');
    }

    private function migrateWhatsAppChannels()
    {
        $channels = DB::connection('rails')
            ->table('channel_whatsapp')
            ->get();

        foreach ($channels as $channel) {
            DB::table('channel_whatsapp')->insert([
                'id' => $channel->id,
                'phone_number' => $channel->phone_number,
                'provider' => $channel->provider ?? 'default',
                'provider_config' => json_decode($channel->provider_config ?? '{}', true),
                'message_templates' => json_decode($channel->message_templates ?? '{}', true),
                'message_templates_last_updated' => $channel->message_templates_last_updated,
                'created_at' => $channel->created_at,
                'updated_at' => $channel->updated_at,
            ]);
        }

        $this->info('Migrated ' . $channels->count() . ' WhatsApp channels');
    }
}
```

### Step 4: Configuration Migration

#### 4.1 Environment Variables

Create a mapping script for environment variables:

```bash
#!/bin/bash
# scripts/migrate-env-vars.sh

# Rails to Laravel environment variable mapping
echo "Migrating environment variables..."

# Database
export DB_CONNECTION=pgsql
export DB_HOST=$POSTGRES_HOST
export DB_PORT=$POSTGRES_PORT
export DB_DATABASE=$POSTGRES_DATABASE
export DB_USERNAME=$POSTGRES_USERNAME
export DB_PASSWORD=$POSTGRES_PASSWORD

# Redis
export REDIS_HOST=$REDIS_URL
export REDIS_PASSWORD=$REDIS_PASSWORD
export REDIS_PORT=6379

# Mail
export MAIL_MAILER=smtp
export MAIL_HOST=$SMTP_ADDRESS
export MAIL_PORT=$SMTP_PORT
export MAIL_USERNAME=$SMTP_USERNAME
export MAIL_PASSWORD=$SMTP_PASSWORD
export MAIL_ENCRYPTION=$SMTP_ENABLE_STARTTLS_AUTO

# Storage
if [ "$ACTIVE_STORAGE_SERVICE" = "amazon" ]; then
    export FILESYSTEM_DISK=s3
    export AWS_ACCESS_KEY_ID=$S3_BUCKET_NAME
    export AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY
    export AWS_DEFAULT_REGION=$AWS_REGION
    export AWS_BUCKET=$S3_BUCKET_NAME
fi

# Third-party services
export OPENAI_API_KEY=$OPENAI_API_KEY
export SLACK_CLIENT_ID=$SLACK_CLIENT_ID
export SLACK_CLIENT_SECRET=$SLACK_CLIENT_SECRET

echo "Environment variables migrated!"
```

#### 4.2 Feature Flags and Settings

```php
<?php
// app/Console/Commands/MigrateSettings.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\GlobalConfigService;

class MigrateSettings extends Command
{
    protected $signature = 'migrate:settings';
    protected $description = 'Migrate global settings and feature flags from Rails';

    public function handle()
    {
        $this->info('Starting settings migration...');

        // Migrate installation config
        $railsConfig = DB::connection('rails')
            ->table('installation_configs')
            ->first();

        if ($railsConfig) {
            $globalConfig = app(GlobalConfigService::class);
            
            // Map Rails settings to Laravel global config
            $settings = [
                'BRAND_NAME' => $railsConfig->brand_name ?? 'Chatwoot',
                'BRAND_URL' => $railsConfig->brand_url ?? config('app.url'),
                'SUPPORT_EMAIL' => $railsConfig->support_email,
                'MAILER_SENDER_EMAIL' => $railsConfig->mailer_sender_email,
                'MAILER_SENDER_NAME' => $railsConfig->mailer_sender_name,
                'DISPLAY_MANIFEST' => $railsConfig->display_manifest ?? true,
                'CREATE_NEW_ACCOUNT_FROM_DASHBOARD' => $railsConfig->create_new_account_from_dashboard ?? false,
                'CHATWOOT_INBOX_TOKEN' => $railsConfig->chatwoot_inbox_token,
                'API_CHANNEL_NAME' => $railsConfig->api_channel_name ?? 'API',
                'API_CHANNEL_THUMBNAIL' => $railsConfig->api_channel_thumbnail,
            ];

            foreach ($settings as $key => $value) {
                if ($value !== null) {
                    $globalConfig->set($key, $value);
                }
            }
        }

        $this->info('Settings migration completed!');
    }
}
```

### Step 5: File Migration

#### 5.1 Attachment Migration

```php
<?php
// app/Console/Commands/MigrateAttachments.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Attachment;

class MigrateAttachments extends Command
{
    protected $signature = 'migrate:attachments';
    protected $description = 'Migrate file attachments from Rails';

    public function handle()
    {
        $this->info('Starting attachment migration...');

        $railsAttachments = DB::connection('rails')
            ->table('active_storage_attachments')
            ->join('active_storage_blobs', 'active_storage_attachments.blob_id', '=', 'active_storage_blobs.id')
            ->select('active_storage_attachments.*', 'active_storage_blobs.filename', 'active_storage_blobs.content_type', 'active_storage_blobs.byte_size', 'active_storage_blobs.key')
            ->get();

        foreach ($railsAttachments as $railsAttachment) {
            // Copy file from Rails storage to Laravel storage
            $railsFilePath = $this->getRailsFilePath($railsAttachment->key);
            $laravelFilePath = 'attachments/' . $railsAttachment->key;

            if (file_exists($railsFilePath)) {
                Storage::put($laravelFilePath, file_get_contents($railsFilePath));

                // Create attachment record
                Attachment::create([
                    'file_type' => $this->mapFileType($railsAttachment->content_type),
                    'external_url' => Storage::url($laravelFilePath),
                    'coordinates_lat' => null,
                    'coordinates_long' => null,
                    'message_id' => $railsAttachment->record_id,
                    'account_id' => $this->getAccountIdFromMessage($railsAttachment->record_id),
                    'file_size' => $railsAttachment->byte_size,
                    'fallback_title' => $railsAttachment->filename,
                    'extension' => pathinfo($railsAttachment->filename, PATHINFO_EXTENSION),
                ]);
            }
        }

        $this->info('Attachment migration completed!');
    }

    private function getRailsFilePath($key)
    {
        // Adjust path based on your Rails storage configuration
        return storage_path("rails_storage/{$key}");
    }

    private function mapFileType($contentType)
    {
        if (str_starts_with($contentType, 'image/')) return 'image';
        if (str_starts_with($contentType, 'audio/')) return 'audio';
        if (str_starts_with($contentType, 'video/')) return 'video';
        return 'file';
    }

    private function getAccountIdFromMessage($messageId)
    {
        $message = DB::table('messages')->where('id', $messageId)->first();
        if ($message) {
            $conversation = DB::table('conversations')->where('id', $message->conversation_id)->first();
            return $conversation ? $conversation->account_id : null;
        }
        return null;
    }
}
```

### Step 6: Validation and Testing

#### 6.1 Data Integrity Validation

```php
<?php
// app/Console/Commands/ValidateMigration.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateMigration extends Command
{
    protected $signature = 'migrate:validate';
    protected $description = 'Validate data migration integrity';

    public function handle()
    {
        $this->info('Starting migration validation...');

        $this->validateCounts();
        $this->validateRelationships();
        $this->validateDataIntegrity();

        $this->info('Migration validation completed!');
    }

    private function validateCounts()
    {
        $this->info('Validating record counts...');

        $tables = ['users', 'accounts', 'contacts', 'conversations', 'messages', 'inboxes'];

        foreach ($tables as $table) {
            $railsCount = DB::connection('rails')->table($table)->count();
            $laravelCount = DB::table($table)->count();

            if ($railsCount === $laravelCount) {
                $this->info("✅ {$table}: {$laravelCount} records (matches Rails)");
            } else {
                $this->error("❌ {$table}: Laravel {$laravelCount} vs Rails {$railsCount}");
            }
        }
    }

    private function validateRelationships()
    {
        $this->info('Validating relationships...');

        // Validate conversation-message relationships
        $orphanedMessages = DB::table('messages')
            ->leftJoin('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->whereNull('conversations.id')
            ->count();

        if ($orphanedMessages === 0) {
            $this->info('✅ All messages have valid conversations');
        } else {
            $this->error("❌ Found {$orphanedMessages} orphaned messages");
        }

        // Validate account-user relationships
        $orphanedAccountUsers = DB::table('account_users')
            ->leftJoin('accounts', 'account_users.account_id', '=', 'accounts.id')
            ->leftJoin('users', 'account_users.user_id', '=', 'users.id')
            ->where(function ($query) {
                $query->whereNull('accounts.id')->orWhereNull('users.id');
            })
            ->count();

        if ($orphanedAccountUsers === 0) {
            $this->info('✅ All account-user relationships are valid');
        } else {
            $this->error("❌ Found {$orphanedAccountUsers} invalid account-user relationships");
        }
    }

    private function validateDataIntegrity()
    {
        $this->info('Validating data integrity...');

        // Sample data validation
        $sampleConversations = DB::table('conversations')->limit(100)->get();

        foreach ($sampleConversations as $conversation) {
            $railsConversation = DB::connection('rails')
                ->table('conversations')
                ->where('id', $conversation->id)
                ->first();

            if (!$railsConversation) {
                $this->error("❌ Conversation {$conversation->id} not found in Rails");
                continue;
            }

            // Validate key fields
            if ($conversation->status !== $railsConversation->status) {
                $this->error("❌ Conversation {$conversation->id} status mismatch");
            }

            if ($conversation->contact_id !== $railsConversation->contact_id) {
                $this->error("❌ Conversation {$conversation->id} contact_id mismatch");
            }
        }

        $this->info('✅ Data integrity validation completed');
    }
}
```

### Step 7: Incremental Sync

For ongoing synchronization during migration:

```php
<?php
// app/Console/Commands/SyncIncrementalChanges.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncIncrementalChanges extends Command
{
    protected $signature = 'migrate:sync {--since=}';
    protected $description = 'Sync incremental changes from Rails since last sync';

    public function handle()
    {
        $since = $this->option('since') ?: Carbon::now()->subHour()->toDateTimeString();
        $this->info("Syncing changes since: {$since}");

        $this->syncNewRecords($since);
        $this->syncUpdatedRecords($since);

        $this->info('Incremental sync completed!');
    }

    private function syncNewRecords($since)
    {
        // Sync new conversations
        $newConversations = DB::connection('rails')
            ->table('conversations')
            ->where('created_at', '>', $since)
            ->get();

        foreach ($newConversations as $conversation) {
            DB::table('conversations')->updateOrInsert(
                ['id' => $conversation->id],
                [
                    'account_id' => $conversation->account_id,
                    'inbox_id' => $conversation->inbox_id,
                    'contact_id' => $conversation->contact_id,
                    'assignee_id' => $conversation->assignee_id,
                    'status' => $conversation->status,
                    'created_at' => $conversation->created_at,
                    'updated_at' => $conversation->updated_at,
                ]
            );
        }

        $this->info("Synced {$newConversations->count()} new conversations");

        // Sync new messages
        $newMessages = DB::connection('rails')
            ->table('messages')
            ->where('created_at', '>', $since)
            ->get();

        foreach ($newMessages as $message) {
            DB::table('messages')->updateOrInsert(
                ['id' => $message->id],
                [
                    'content' => $message->content,
                    'message_type' => $message->message_type,
                    'conversation_id' => $message->conversation_id,
                    'sender_type' => $message->sender_type,
                    'sender_id' => $message->sender_id,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ]
            );
        }

        $this->info("Synced {$newMessages->count()} new messages");
    }

    private function syncUpdatedRecords($since)
    {
        // Sync updated conversations
        $updatedConversations = DB::connection('rails')
            ->table('conversations')
            ->where('updated_at', '>', $since)
            ->where('created_at', '<=', $since)
            ->get();

        foreach ($updatedConversations as $conversation) {
            DB::table('conversations')
                ->where('id', $conversation->id)
                ->update([
                    'status' => $conversation->status,
                    'assignee_id' => $conversation->assignee_id,
                    'priority' => $conversation->priority,
                    'updated_at' => $conversation->updated_at,
                ]);
        }

        $this->info("Synced {$updatedConversations->count()} updated conversations");
    }
}
```

## Feature Mapping

### Core Features

| Rails Feature | Laravel Implementation | Status | Notes |
|---------------|----------------------|--------|-------|
| User Management | User model + Sanctum auth | ✅ Complete | Enhanced with MFA |
| Account Management | Account model + policies | ✅ Complete | Multi-tenancy preserved |
| Conversations | Conversation model + API | ✅ Complete | All features migrated |
| Messages | Message model + broadcasting | ✅ Complete | Real-time via Reverb |
| Contacts | Contact model + search | ✅ Complete | Enhanced search capabilities |
| Inboxes | Inbox model + channels | ✅ Complete | All channel types supported |
| Teams | Team model + assignments | ✅ Complete | Role-based access |
| Labels | Label model + filtering | ✅ Complete | Color coding preserved |

### Channel Integrations

| Channel | Rails Implementation | Laravel Implementation | Status |
|---------|---------------------|----------------------|--------|
| Web Widget | ActionCable + JS | Reverb + JS | ✅ Complete |
| Email | ActionMailer + IMAP | Laravel Mail + IMAP | ✅ Complete |
| WhatsApp | WhatsApp Cloud API | WhatsApp Cloud API | ✅ Complete |
| Facebook | Graph API | Graph API | ✅ Complete |
| Twitter | Twitter API v1.1 | Twitter API v2 | ✅ Enhanced |
| Telegram | Telegram Bot API | Telegram Bot API | ✅ Complete |
| SMS | Twilio API | Twilio API | ✅ Complete |
| LINE | LINE Messaging API | LINE Messaging API | ✅ Complete |

### Third-Party Integrations

| Integration | Rails Implementation | Laravel Implementation | Status |
|-------------|---------------------|----------------------|--------|
| Slack | Slack Web API | Slack Web API | ✅ Complete |
| Linear | GraphQL API | GraphQL API | ✅ Complete |
| Shopify | Admin API | Admin API | ✅ Complete |
| Dialogflow | REST API | REST API | ✅ Complete |
| OpenAI | OpenAI API | OpenAI API | ✅ Enhanced |

### Enterprise Features

| Feature | Rails Implementation | Laravel Implementation | Status |
|---------|---------------------|----------------------|--------|
| SAML SSO | OmniAuth SAML | Laravel SAML2 | ✅ Complete |
| Custom Roles | Pundit policies | Spatie Permission | ✅ Enhanced |
| SLA Policies | Custom implementation | Enhanced tracking | ✅ Complete |
| Audit Logs | Paper Trail | Spatie Activity Log | ✅ Enhanced |

## API Endpoint Mapping

### Authentication Endpoints

| Rails Endpoint | Laravel Endpoint | Method | Status |
|----------------|------------------|--------|--------|
| `/auth/sign_in` | `/api/v1/auth/login` | POST | ✅ |
| `/auth/sign_out` | `/api/v1/auth/logout` | POST | ✅ |
| `/auth/sign_up` | `/api/v1/auth/register` | POST | ✅ |
| `/auth/password` | `/api/v1/auth/password/reset` | POST | ✅ |

### Core Resource Endpoints

| Rails Pattern | Laravel Pattern | Status |
|---------------|-----------------|--------|
| `/api/v1/accounts/:id/conversations` | `/api/v1/accounts/{account}/conversations` | ✅ |
| `/api/v1/accounts/:id/contacts` | `/api/v1/accounts/{account}/contacts` | ✅ |
| `/api/v1/accounts/:id/inboxes` | `/api/v1/accounts/{account}/inboxes` | ✅ |
| `/api/v1/accounts/:id/teams` | `/api/v1/accounts/{account}/teams` | ✅ |

### Widget API Endpoints

| Rails Endpoint | Laravel Endpoint | Status |
|----------------|------------------|--------|
| `/public/api/v1/inboxes/:inbox_identifier/contacts` | `/api/v1/public/inboxes/{identifier}/contacts` | ✅ |
| `/public/api/v1/inboxes/:inbox_identifier/conversations` | `/api/v1/public/inboxes/{identifier}/conversations` | ✅ |

### Super Admin Endpoints

| Rails Pattern | Laravel Pattern | Status |
|---------------|-----------------|--------|
| `/super_admin/*` | `/api/v1/super_admin/*` | ✅ Enhanced |

## Configuration Migration

### Environment Variables

| Rails Variable | Laravel Variable | Notes |
|----------------|------------------|-------|
| `POSTGRES_HOST` | `DB_HOST` | Direct mapping |
| `POSTGRES_DATABASE` | `DB_DATABASE` | Direct mapping |
| `REDIS_URL` | `REDIS_HOST` | Parse URL for host |
| `SMTP_ADDRESS` | `MAIL_HOST` | Direct mapping |
| `S3_BUCKET_NAME` | `AWS_BUCKET` | AWS S3 configuration |
| `FRONTEND_URL` | `APP_URL` | Application URL |

### Feature Flags

| Rails Feature Flag | Laravel Configuration | Location |
|-------------------|----------------------|----------|
| `DISPLAY_MANIFEST` | `config('app.display_manifest')` | config/app.php |
| `CREATE_NEW_ACCOUNT_FROM_DASHBOARD` | Global config | Database |
| `CHATWOOT_INBOX_TOKEN` | Global config | Database |

## Testing and Validation

### Pre-Migration Testing

1. **Backup Verification**
   ```bash
   # Test backup restoration
   pg_dump chatwoot_production > backup_test.sql
   createdb chatwoot_test
   psql chatwoot_test < backup_test.sql
   ```

2. **Data Integrity Checks**
   ```bash
   # Run validation scripts
   php artisan migrate:validate
   ```

3. **API Compatibility Testing**
   ```bash
   # Run API tests against both systems
   php artisan test --testsuite=Migration
   ```

### Post-Migration Testing

1. **Functional Testing**
   - Test all major user workflows
   - Verify channel integrations
   - Test real-time features
   - Validate third-party integrations

2. **Performance Testing**
   - Compare response times
   - Test under load
   - Monitor resource usage
   - Validate queue processing

3. **Data Validation**
   - Compare record counts
   - Validate data integrity
   - Test search functionality
   - Verify file attachments

## Rollback Procedures

### Emergency Rollback

If critical issues are discovered:

1. **Immediate Actions**
   ```bash
   # Switch traffic back to Rails
   # Update load balancer or DNS
   
   # Put Laravel in maintenance mode
   php artisan down --message="System maintenance in progress"
   ```

2. **Data Synchronization**
   ```bash
   # Sync any new data from Laravel back to Rails
   php artisan migrate:rollback-sync
   ```

3. **Service Restoration**
   ```bash
   # Restart Rails services
   # Verify Rails system functionality
   # Monitor for issues
   ```

### Planned Rollback

For planned rollback during testing:

1. **Data Export**
   ```bash
   # Export any test data created in Laravel
   php artisan migrate:export-test-data
   ```

2. **Clean Rollback**
   ```bash
   # Reset Laravel database
   php artisan migrate:fresh
   
   # Switch traffic back to Rails
   # Update configurations
   ```

## Post-Migration Tasks

### Immediate Tasks (Day 1)

1. **System Monitoring**
   - Monitor error rates
   - Check performance metrics
   - Verify all integrations
   - Monitor queue processing

2. **User Communication**
   - Notify users of migration completion
   - Provide updated documentation
   - Set up support channels

3. **Data Validation**
   - Run comprehensive data checks
   - Verify critical workflows
   - Test edge cases

### Short-term Tasks (Week 1)

1. **Performance Optimization**
   - Optimize slow queries
   - Tune cache settings
   - Adjust queue configurations

2. **User Feedback**
   - Collect user feedback
   - Address reported issues
   - Document lessons learned

3. **Documentation Updates**
   - Update API documentation
   - Create troubleshooting guides
   - Document new features

### Long-term Tasks (Month 1)

1. **Feature Enhancements**
   - Implement Laravel-specific improvements
   - Add new features not available in Rails
   - Optimize for Laravel ecosystem

2. **Training and Adoption**
   - Train support team on new system
   - Create user guides
   - Conduct training sessions

3. **System Optimization**
   - Fine-tune performance
   - Implement advanced monitoring
   - Plan future enhancements

## Migration Checklist

### Pre-Migration

- [ ] Complete system audit
- [ ] Document custom modifications
- [ ] Set up Laravel environment
- [ ] Create migration scripts
- [ ] Test migration scripts on sample data
- [ ] Prepare rollback procedures
- [ ] Schedule migration window
- [ ] Notify stakeholders

### During Migration

- [ ] Put Rails in maintenance mode
- [ ] Run database backup
- [ ] Execute migration scripts
- [ ] Validate data integrity
- [ ] Test critical functionality
- [ ] Update DNS/load balancer
- [ ] Monitor system health

### Post-Migration

- [ ] Verify all features working
- [ ] Test integrations
- [ ] Monitor performance
- [ ] Collect user feedback
- [ ] Address any issues
- [ ] Update documentation
- [ ] Plan optimization tasks

## Support and Resources

### Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [ClearLine API Documentation](./API_DOCUMENTATION.md)
- [Deployment Guide](./DEPLOYMENT_GUIDE.md)

### Migration Support
- Development team contact information
- Emergency escalation procedures
- Community resources and forums

### Monitoring and Alerts
- System health dashboards
- Error tracking and alerting
- Performance monitoring tools

---

**Last Updated:** 2025-01-02  
**Version:** 1.0  
**Migration Team:** Development Team