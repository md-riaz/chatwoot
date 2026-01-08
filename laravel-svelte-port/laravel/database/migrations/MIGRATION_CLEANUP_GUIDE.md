# Migration Cleanup Guide

## ❌ Files to Remove from Original Migrations

The following files from `custom/laravel/database/migrations/` should be **REMOVED** because they conflict with the organized migrations or are redundant:

### 1. Laravel Default Framework Files (Conflicting)
```bash
# These are replaced by our organized framework migration
rm custom/laravel/database/migrations/0001_01_01_000000_create_users_table.php
rm custom/laravel/database/migrations/0001_01_01_000001_create_cache_table.php  
rm custom/laravel/database/migrations/0001_01_01_000002_create_jobs_table.php
```

### 2. Duplicate Table Migrations (Conflicting)
```bash
# These create the same tables as our organized migrations
rm custom/laravel/database/migrations/2024_01_01_000001_create_accounts_table.php
rm custom/laravel/database/migrations/2024_01_01_000002_create_companies_table.php
rm custom/laravel/database/migrations/2024_01_01_000003_create_contacts_table.php
rm custom/laravel/database/migrations/2024_01_01_000004_create_inboxes_table.php
rm custom/laravel/database/migrations/2024_01_01_000005_create_channels_tables.php
rm custom/laravel/database/migrations/2024_01_01_000006_create_agent_bots_table.php
rm custom/laravel/database/migrations/2024_01_01_000007_create_teams_table.php
rm custom/laravel/database/migrations/2024_01_01_000008_create_campaigns_table.php
rm custom/laravel/database/migrations/2024_01_01_000009_create_sla_policies_table.php
rm custom/laravel/database/migrations/2024_01_01_000010_create_contact_inboxes_table.php
rm custom/laravel/database/migrations/2024_01_01_000011_create_conversations_table.php
rm custom/laravel/database/migrations/2024_01_01_000011_create_labels_table.php
rm custom/laravel/database/migrations/2024_01_01_000012_create_applied_slas_table.php
rm custom/laravel/database/migrations/2024_01_01_000012_create_automation_rules_table.php
rm custom/laravel/database/migrations/2024_01_01_000012_create_canned_responses_table.php
rm custom/laravel/database/migrations/2024_01_01_000013_create_messages_table.php
rm custom/laravel/database/migrations/2024_01_01_000013_create_webhooks_table.php
rm custom/laravel/database/migrations/2024_01_01_000014_create_notifications_table.php
rm custom/laravel/database/migrations/2024_01_01_000015_create_attachments_and_mentions_tables.php
rm custom/laravel/database/migrations/2024_01_01_000022_create_help_center_tables.php
rm custom/laravel/database/migrations/2024_01_01_000029_create_additional_channel_tables.php
rm custom/laravel/database/migrations/2024_01_01_000039_create_account_users_table.php
rm custom/laravel/database/migrations/2024_01_01_000039_create_custom_roles_table.php
rm custom/laravel/database/migrations/2024_01_01_000043_create_additional_channels_table.php
```

### 3. Multi-Table Migrations (Should be split)
```bash
# These create multiple tables in one file - replaced by individual table migrations
rm custom/laravel/database/migrations/2024_01_01_000045_create_access_tokens_and_action_mailbox_table.php
rm custom/laravel/database/migrations/2024_01_01_000046_create_active_storage_tables.php
rm custom/laravel/database/migrations/2024_01_01_000050_create_tags_and_taggings_tables.php
```

## ✅ Files to Keep (Non-conflicting)

These files can remain as they add functionality not covered in the organized migrations:

### Column Addition Migrations
```bash
# These modify existing tables - keep them
2024_01_01_000044_add_translations_to_messages_table.php
2024_01_02_000001_add_email_verification_to_users_table.php
2024_05_18_000001_add_dispatched_at_to_campaigns_table.php
2024_05_18_000002_change_subscription_type_to_string_on_notification_subscriptions.php
2025_01_02_120000_add_saml_fields_to_users_table.php
2025_01_02_121000_add_sla_status_to_applied_slas_table.php
2025_12_31_070243_add_webhook_secret_to_channel_telegram.php
2025_12_31_084737_update_channel_provider_configs.php
```

### Performance & Index Migrations
```bash
# These add indexes and optimizations - keep them
2025_01_02_140000_add_performance_optimization_indexes.php
2025_01_02_150000_enhance_search_performance_indexes.php
```

### Update Migrations
```bash
# These update existing table structures - keep them
2025_01_03_000001_update_integration_hooks_table.php
2025_01_03_000004_update_channel_tiktok_table.php
```

### Specialized Feature Migrations
```bash
# These create specialized tables not in core organized migrations - keep them
2024_01_01_000016_create_media_table.php
2024_01_01_000018_create_macros_table.php
2024_01_01_000020_create_custom_filters_table.php
2024_01_01_000021_create_notes_table.php
2024_01_01_000023_create_csat_survey_responses_table.php
2024_01_01_000024_create_custom_attribute_definitions_table.php
2024_01_01_000025_create_reporting_events_table.php
2024_01_01_000026_create_conversation_participants_table.php
2024_01_01_000027_create_dashboard_apps_table.php
2024_01_01_000028_create_working_hours_table.php
2024_01_01_000030_create_sanctum_personal_access_tokens_table.php
2024_01_01_000031_create_permission_tables.php
2024_01_01_000032_create_activity_log_table.php
2024_01_01_000034_create_segments_table.php
2024_01_01_000035_create_integrations_table.php
2024_01_01_000036_create_super_admin_tables.php
2024_01_01_000037_create_audit_logs_table.php
2024_01_01_000040_create_assignment_policies_table.php
2024_01_01_000041_create_agent_capacity_policies_table.php
2024_01_01_000042_create_notification_settings_table.php
2024_01_01_000047_create_audits_table.php
2024_01_01_000048_create_notification_subscriptions_table.php
2024_01_01_000049_create_sla_events_table.php
2024_01_01_000051_create_channel_tiktok_table.php
2025_01_03_000002_create_access_tokens_table.php
2025_01_03_000003_create_channel_voice_table.php
2025_12_30_000000_create_facebook_message_events_table.php
2025_12_30_000001_create_article_embeddings_table.php
2025_12_30_000002_create_data_imports_table.php
2025_12_30_000003_create_email_templates_table.php
2025_12_30_000004_create_leaves_table.php
2025_12_30_000005_create_portal_members_table.php
2025_12_30_000006_create_related_categories_table.php
```

## 🚀 Migration Strategy

### Option 1: Clean Slate (Recommended)
```bash
# 1. Backup current database
php artisan db:backup

# 2. Reset all migrations
php artisan migrate:reset

# 3. Remove conflicting files (see list above)
# 4. Copy organized migrations to main migrations folder
cp custom/laravel/database/migrations_organized/*.php database/migrations/

# 5. Run fresh migrations
php artisan migrate
```

### Option 2: Gradual Migration
```bash
# 1. Create new migration environment
# 2. Test organized migrations in isolation
# 3. Gradually replace old migrations with organized ones
# 4. Ensure data migration scripts for existing data
```

## ⚠️ Important Notes

1. **The `migrations` table is automatically managed by Laravel** - never create it manually
2. **Always backup your database** before making migration changes
3. **Test in development environment first** before applying to production
4. **Data migration may be required** if you have existing data in conflicting table structures

## ✨ Result After Cleanup

After removing the conflicting files and using the organized migrations:
- ✅ No circular dependencies
- ✅ Clean, single-responsibility migrations  
- ✅ Proper dependency ordering
- ✅ Laravel framework compatibility
- ✅ No duplicate table definitions