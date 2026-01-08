# Laravel Migration Reorganization Plan

## Overview
This reorganization creates individual migration files for each table, ordered to prevent circular dependencies. Each migration file contains only one table definition with proper foreign key relationships.

## Dependency Order Strategy

### 1. Core Foundation Tables (No Dependencies)
- `2024_01_01_000001_create_users_table.php` - Authentication core ✅
- `2024_01_01_000002_create_accounts_table.php` - Multi-tenancy core ✅

### 2. Supporting Tables (Depend on Core)
- `2024_01_01_000003_create_companies_table.php` - Depends on: accounts ✅
- `2024_01_01_000004_create_custom_roles_table.php` - Depends on: accounts ✅
- `2024_01_01_000005_create_agent_capacity_policies_table.php` - Depends on: accounts ✅

### 3. Junction/Relationship Tables
- `2024_01_01_000006_create_account_users_table.php` - Depends on: users, accounts, custom_roles, agent_capacity_policies ✅
- `2024_01_01_000007_create_contacts_table.php` - Depends on: accounts, companies ✅
- `2024_01_01_000008_create_teams_table.php` - Depends on: accounts ✅
- `2024_01_01_000009_create_team_members_table.php` - Depends on: teams, users ✅
- `2024_01_01_000010_create_labels_table.php` - Depends on: accounts ✅

### 4. Channel Tables (Must be before inboxes)
- `2024_01_01_000011_create_channel_web_widgets_table.php` ✅
- `2024_01_01_000012_create_channel_email_table.php` ✅
- `2024_01_01_000013_create_channel_api_table.php` ✅
- `2024_01_01_000014_create_channel_whatsapp_table.php` ✅
- `2024_01_01_000015_create_channel_telegram_table.php` ✅
- `2024_01_01_000016_create_channel_sms_table.php` ✅
- `2024_01_01_000017_create_channel_twilio_sms_table.php` ✅
- `2024_01_01_000018_create_channel_line_table.php` ✅
- `2024_01_01_000019_create_channel_facebook_pages_table.php` ✅
- `2024_01_01_000020_create_channel_twitter_profiles_table.php` ✅
- `2024_01_01_000021_create_channel_instagram_table.php` ✅
- `2024_01_01_000022_create_channel_voice_table.php` ✅
- `2024_01_01_000023_create_channel_tiktok_table.php` ✅

### 5. Inbox and Communication Tables
- `2024_01_01_000024_create_inboxes_table.php` - Depends on: accounts, channels (polymorphic) ✅
- `2024_01_01_000025_create_contact_inboxes_table.php` - Depends on: contacts, inboxes ✅
- `2024_01_01_000026_create_campaigns_table.php` - Depends on: accounts, inboxes, users ✅
- `2024_01_01_000027_create_conversations_table.php` - Depends on: accounts, inboxes, contacts, teams, users, campaigns ✅
- `2024_01_01_000028_create_messages_table.php` - Depends on: conversations, users ✅

### 6. Bot and Automation Tables
- `2024_01_01_000029_create_agent_bots_table.php` - Depends on: accounts ✅
- `2024_01_01_000030_create_agent_bot_inboxes_table.php` - Depends on: agent_bots, inboxes ✅
- `2024_01_01_000031_create_sla_policies_table.php` - Depends on: accounts ✅
- `2024_01_01_000032_create_applied_slas_table.php` - Depends on: accounts, sla_policies, conversations ✅
- `2024_01_01_000033_create_sla_events_table.php` - Depends on: accounts, applied_slas ✅
- `2024_01_01_000034_create_automation_rules_table.php` - Depends on: accounts ✅
- `2024_01_01_000035_create_webhooks_table.php` - Depends on: accounts, inboxes ✅

### 7. Message Related Tables
- `2024_01_01_000036_create_attachments_table.php` - Depends on: messages, accounts ✅
- `2024_01_01_000037_create_mentions_table.php` - Depends on: users, conversations, accounts ✅

### 8. Help Center Tables
- `2024_01_01_000038_create_portals_table.php` - Depends on: accounts ✅
- `2024_01_01_000039_create_categories_table.php` - Depends on: accounts, portals ✅
- `2024_01_01_000040_create_folders_table.php` - Depends on: accounts, categories ✅
- `2024_01_01_000041_create_articles_table.php` - Depends on: accounts, portals, categories, folders, users ✅

### 9. Feature Tables
- `2024_01_01_000042_create_canned_responses_table.php` - Depends on: accounts ✅
- `2024_01_01_000043_create_macros_table.php` - Depends on: accounts, users ✅
- `2024_01_01_000044_create_notifications_table.php` - Depends on: accounts, users ✅
- `2024_01_01_000045_create_custom_filters_table.php` - Depends on: accounts, users ✅
- `2024_01_01_000046_create_notes_table.php` - Depends on: accounts, contacts, users ✅
- `2024_01_01_000047_create_csat_survey_responses_table.php` - Depends on: accounts, conversations, messages, contacts, users ✅
- `2024_01_01_000048_create_custom_attribute_definitions_table.php` - Depends on: accounts ✅
- `2024_01_01_000049_create_reporting_events_table.php` - Depends on: accounts, inboxes, users, conversations ✅
- `2024_01_01_000050_create_conversation_participants_table.php` - Depends on: accounts, users, conversations ✅

### 10. System Tables (To be created)
- `2024_01_01_000051_create_dashboard_apps_table.php`
- `2024_01_01_000052_create_working_hours_table.php`
- `2024_01_01_000053_create_notification_settings_table.php`
- `2024_01_01_000054_create_notification_subscriptions_table.php`

### 11. Laravel Framework Tables (To be created)
- `2024_01_01_000055_create_personal_access_tokens_table.php` (Sanctum)
- `2024_01_01_000056_create_permission_tables.php` (Spatie)
- `2024_01_01_000057_create_activity_log_table.php` (Spatie)
- `2024_01_01_000058_create_media_table.php` (Spatie)

### 12. Additional System Tables (To be created)
- `2024_01_01_000059_create_access_tokens_table.php`
- `2024_01_01_000060_create_audits_table.php`
- `2024_01_01_000061_create_tags_table.php`
- `2024_01_01_000062_create_taggings_table.php`

## Benefits of This Organization

1. **Clear Dependencies**: Each table's dependencies are obvious from the order
2. **No Circular References**: Tables are created in dependency order
3. **Single Responsibility**: Each migration handles exactly one table
4. **Easy Maintenance**: Individual table changes don't affect others
5. **Better Rollbacks**: Can rollback individual tables without affecting others
6. **Clear Documentation**: Each migration is self-documenting

## Migration Commands

To use these organized migrations:

```bash
# Clear existing migrations (backup first!)
php artisan migrate:reset

# Run organized migrations
php artisan migrate --path=database/migrations_organized
```

## Notes

- All foreign key constraints are properly defined
- Indexes are added for performance
- Unique constraints prevent data integrity issues
- Timestamps and soft deletes are included where appropriate
- JSON columns use proper defaults for PostgreSQL compatibility