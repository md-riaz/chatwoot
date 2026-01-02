<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Optimize conversations table indexes
        Schema::table('conversations', function (Blueprint $table) {
            // Composite indexes for common query patterns
            $table->index(['account_id', 'status', 'priority', 'last_activity_at'], 'conversations_account_status_priority_idx');
            $table->index(['assignee_id', 'status', 'last_activity_at'], 'conversations_assignee_status_idx');
            $table->index(['team_id', 'status', 'last_activity_at'], 'conversations_team_status_idx');
            $table->index(['inbox_id', 'status', 'priority', 'created_at'], 'conversations_inbox_status_priority_idx');
            $table->index(['contact_id', 'status'], 'conversations_contact_status_idx');
            $table->index(['sla_policy_id', 'status'], 'conversations_sla_status_idx');
        });

        // Optimize messages table indexes
        Schema::table('messages', function (Blueprint $table) {
            // Composite indexes for message queries
            $table->index(['conversation_id', 'created_at', 'message_type'], 'messages_conversation_created_type_idx');
            $table->index(['sender_id', 'sender_type', 'created_at'], 'messages_sender_created_idx');
            $table->index(['account_id', 'inbox_id', 'created_at'], 'messages_account_inbox_created_idx');
            $table->index(['content_type', 'private'], 'messages_content_type_private_idx');
            $table->index(['status', 'created_at'], 'messages_status_created_idx');
        });

        // Optimize reporting_events table indexes
        Schema::table('reporting_events', function (Blueprint $table) {
            // Composite indexes for reporting queries
            $table->index(['account_id', 'name', 'created_at'], 'reporting_events_account_name_created_idx');
            $table->index(['conversation_id', 'name', 'event_start_time'], 'reporting_events_conversation_name_start_idx');
            $table->index(['user_id', 'name', 'created_at'], 'reporting_events_user_name_created_idx');
            $table->index(['inbox_id', 'name', 'created_at'], 'reporting_events_inbox_name_created_idx');
            $table->index(['name', 'event_start_time', 'event_end_time'], 'reporting_events_name_time_range_idx');
        });

        // Optimize contacts table indexes
        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->index(['account_id', 'email'], 'contacts_account_email_idx');
                $table->index(['account_id', 'phone_number'], 'contacts_account_phone_idx');
                $table->index(['account_id', 'created_at'], 'contacts_account_created_idx');
                $table->index(['account_id', 'last_activity_at'], 'contacts_account_activity_idx');
            });
        }

        // Optimize inboxes table indexes
        if (Schema::hasTable('inboxes')) {
            Schema::table('inboxes', function (Blueprint $table) {
                $table->index(['account_id', 'channel_type'], 'inboxes_account_channel_idx');
                $table->index(['account_id', 'enabled'], 'inboxes_account_enabled_idx');
            });
        }

        // Optimize users table indexes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['email', 'email_verified_at'], 'users_email_verified_idx');
                $table->index(['created_at'], 'users_created_idx');
            });
        }

        // Optimize account_users table indexes
        if (Schema::hasTable('account_users')) {
            Schema::table('account_users', function (Blueprint $table) {
                $table->index(['account_id', 'user_id', 'role'], 'account_users_account_user_role_idx');
                $table->index(['user_id', 'active_at'], 'account_users_user_active_idx');
            });
        }

        // Optimize notifications table indexes
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_id', 'notifiable_type', 'read_at'], 'notifications_notifiable_read_idx');
                $table->index(['created_at'], 'notifications_created_idx');
            });
        }

        // Optimize attachments table indexes
        if (Schema::hasTable('attachments')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->index(['message_id', 'file_type'], 'attachments_message_type_idx');
                $table->index(['account_id', 'created_at'], 'attachments_account_created_idx');
            });
        }

        // Add full-text search indexes for PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS messages_content_fulltext_idx ON messages USING gin(to_tsvector(\'english\', content))');
            DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS contacts_name_fulltext_idx ON contacts USING gin(to_tsvector(\'english\', name))');
        }

        // Add full-text search indexes for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE messages ADD FULLTEXT(content)');
            DB::statement('ALTER TABLE contacts ADD FULLTEXT(name, email)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop conversations indexes
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_account_status_priority_idx');
            $table->dropIndex('conversations_assignee_status_idx');
            $table->dropIndex('conversations_team_status_idx');
            $table->dropIndex('conversations_inbox_status_priority_idx');
            $table->dropIndex('conversations_contact_status_idx');
            $table->dropIndex('conversations_sla_status_idx');
        });

        // Drop messages indexes
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_created_type_idx');
            $table->dropIndex('messages_sender_created_idx');
            $table->dropIndex('messages_account_inbox_created_idx');
            $table->dropIndex('messages_content_type_private_idx');
            $table->dropIndex('messages_status_created_idx');
        });

        // Drop reporting_events indexes
        Schema::table('reporting_events', function (Blueprint $table) {
            $table->dropIndex('reporting_events_account_name_created_idx');
            $table->dropIndex('reporting_events_conversation_name_start_idx');
            $table->dropIndex('reporting_events_user_name_created_idx');
            $table->dropIndex('reporting_events_inbox_name_created_idx');
            $table->dropIndex('reporting_events_name_time_range_idx');
        });

        // Drop other table indexes
        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropIndex('contacts_account_email_idx');
                $table->dropIndex('contacts_account_phone_idx');
                $table->dropIndex('contacts_account_created_idx');
                $table->dropIndex('contacts_account_activity_idx');
            });
        }

        if (Schema::hasTable('inboxes')) {
            Schema::table('inboxes', function (Blueprint $table) {
                $table->dropIndex('inboxes_account_channel_idx');
                $table->dropIndex('inboxes_account_enabled_idx');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_email_verified_idx');
                $table->dropIndex('users_created_idx');
            });
        }

        if (Schema::hasTable('account_users')) {
            Schema::table('account_users', function (Blueprint $table) {
                $table->dropIndex('account_users_account_user_role_idx');
                $table->dropIndex('account_users_user_active_idx');
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_notifiable_read_idx');
                $table->dropIndex('notifications_created_idx');
            });
        }

        if (Schema::hasTable('attachments')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropIndex('attachments_message_type_idx');
                $table->dropIndex('attachments_account_created_idx');
            });
        }

        // Drop full-text indexes
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS messages_content_fulltext_idx');
            DB::statement('DROP INDEX IF EXISTS contacts_name_fulltext_idx');
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE messages DROP INDEX content');
            DB::statement('ALTER TABLE contacts DROP INDEX name');
        }
    }
};