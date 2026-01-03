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
        // Add additional search-optimized indexes for messages
        Schema::table('messages', function (Blueprint $table) {
            // Time-based search optimization (3-month window)
            $table->index(['account_id', 'created_at', 'private'], 'messages_account_time_private_idx');
            
            // Conversation-based message search
            $table->index(['conversation_id', 'private', 'created_at'], 'messages_conversation_private_time_idx');
            
            // Content type filtering for search
            $table->index(['account_id', 'content_type', 'private', 'created_at'], 'messages_account_content_search_idx');
        });

        // Add search-optimized indexes for conversations
        Schema::table('conversations', function (Blueprint $table) {
            // Display ID search optimization
            $table->index(['account_id', 'display_id'], 'conversations_account_display_idx');
            
            // Inbox-based conversation search
            $table->index(['inbox_id', 'created_at'], 'conversations_inbox_created_idx');
        });

        // Add search-optimized indexes for contacts
        Schema::table('contacts', function (Blueprint $table) {
            // Multi-field contact search optimization
            $table->index(['account_id', 'name'], 'contacts_account_name_idx');
            $table->index(['account_id', 'identifier'], 'contacts_account_identifier_idx');
        });

        // Enhanced full-text search indexes
        $this->createAdvancedFullTextIndexes();

        // Add articles table indexes if it exists
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->index(['account_id', 'status', 'created_at'], 'articles_account_status_created_idx');
            });

            // Full-text search for articles
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS articles_content_gin_idx ON articles USING gin((to_tsvector(\'english\', COALESCE(title, \'\')) || to_tsvector(\'english\', COALESCE(content, \'\'))))');
            } elseif (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE articles ADD FULLTEXT articles_content_fulltext_idx (title, content)');
            }
        }

        // Add contact_inboxes optimization for permission filtering
        if (Schema::hasTable('contact_inboxes')) {
            Schema::table('contact_inboxes', function (Blueprint $table) {
                $table->index(['contact_id', 'inbox_id'], 'contact_inboxes_contact_inbox_idx');
                $table->index(['inbox_id', 'contact_id'], 'contact_inboxes_inbox_contact_idx');
            });
        }

        // Add inbox_members optimization for permission filtering
        if (Schema::hasTable('inbox_members')) {
            Schema::table('inbox_members', function (Blueprint $table) {
                $table->index(['user_id', 'inbox_id'], 'inbox_members_user_inbox_idx');
                $table->index(['inbox_id', 'user_id'], 'inbox_members_inbox_user_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop messages indexes
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_account_time_private_idx');
            $table->dropIndex('messages_conversation_private_time_idx');
            $table->dropIndex('messages_account_content_search_idx');
        });

        // Drop conversations indexes
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conversations_account_display_idx');
            $table->dropIndex('conversations_inbox_created_idx');
        });

        // Drop contacts indexes
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_account_name_idx');
            $table->dropIndex('contacts_account_identifier_idx');
        });

        // Drop PostgreSQL GIN indexes
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS messages_content_gin_idx');
            DB::statement('DROP INDEX IF EXISTS contacts_search_gin_idx');
            DB::statement('DROP INDEX IF EXISTS conversations_active_search_idx');
            
            if (Schema::hasTable('articles')) {
                DB::statement('DROP INDEX IF EXISTS articles_content_gin_idx');
            }
        }

        // Drop MySQL full-text indexes
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE messages DROP INDEX messages_content_fulltext_idx');
            } catch (\Exception $e) {
                // Index might not exist, ignore error
            }
            
            try {
                DB::statement('ALTER TABLE contacts DROP INDEX contacts_search_fulltext_idx');
            } catch (\Exception $e) {
                // Index might not exist, ignore error
            }
            
            if (Schema::hasTable('articles')) {
                try {
                    DB::statement('ALTER TABLE articles DROP INDEX articles_content_fulltext_idx');
                } catch (\Exception $e) {
                    // Index might not exist, ignore error
                }
            }
        }

        // Drop articles indexes
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropIndex('articles_account_status_created_idx');
            });
        }

        // Drop contact_inboxes indexes
        if (Schema::hasTable('contact_inboxes')) {
            Schema::table('contact_inboxes', function (Blueprint $table) {
                $table->dropIndex('contact_inboxes_contact_inbox_idx');
                $table->dropIndex('contact_inboxes_inbox_contact_idx');
            });
        }

        // Drop inbox_members indexes
        if (Schema::hasTable('inbox_members')) {
            Schema::table('inbox_members', function (Blueprint $table) {
                $table->dropIndex('inbox_members_user_inbox_idx');
                $table->dropIndex('inbox_members_inbox_user_idx');
            });
        }
    }

    /**
     * Create advanced full-text search indexes outside of transaction for PostgreSQL
     */
    private function createAdvancedFullTextIndexes(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Create GIN indexes for better full-text search performance
            try {
                DB::statement('CREATE INDEX IF NOT EXISTS messages_content_gin_idx ON messages USING gin(to_tsvector(\'english\', content)) WHERE content IS NOT NULL AND content != \'\'');
            } catch (\Exception $e) {
                // Index might already exist, ignore error
            }
            
            try {
                // Create GIN index for contact search
                DB::statement('CREATE INDEX IF NOT EXISTS contacts_search_gin_idx ON contacts USING gin((to_tsvector(\'english\', COALESCE(name, \'\')) || to_tsvector(\'english\', COALESCE(email, \'\')) || to_tsvector(\'english\', COALESCE(identifier, \'\'))))');
            } catch (\Exception $e) {
                // Index might already exist, ignore error
            }
            
            try {
                // Create partial index for active conversations
                DB::statement('CREATE INDEX IF NOT EXISTS conversations_active_search_idx ON conversations (account_id, display_id, created_at) WHERE status != 1'); // Assuming 1 is resolved status
            } catch (\Exception $e) {
                // Index might already exist, ignore error
            }
        }

        if (DB::getDriverName() === 'mysql') {
            try {
                // Add full-text index for messages with better configuration
                DB::statement('ALTER TABLE messages ADD FULLTEXT messages_content_fulltext_idx (content) WITH PARSER ngram');
            } catch (\Exception $e) {
                // Index might already exist, ignore error
            }
            
            try {
                // Add full-text index for contacts
                DB::statement('ALTER TABLE contacts ADD FULLTEXT contacts_search_fulltext_idx (name, email, identifier) WITH PARSER ngram');
            } catch (\Exception $e) {
                // Index might already exist, ignore error
            }
        }

        // Add articles table indexes if it exists
        if (Schema::hasTable('articles')) {
            if (DB::getDriverName() === 'pgsql') {
                try {
                    DB::statement('CREATE INDEX IF NOT EXISTS articles_content_gin_idx ON articles USING gin((to_tsvector(\'english\', COALESCE(title, \'\')) || to_tsvector(\'english\', COALESCE(content, \'\'))))');
                } catch (\Exception $e) {
                    // Index might already exist, ignore error
                }
            } elseif (DB::getDriverName() === 'mysql') {
                try {
                    DB::statement('ALTER TABLE articles ADD FULLTEXT articles_content_fulltext_idx (title, content)');
                } catch (\Exception $e) {
                    // Index might already exist, ignore error
                }
            }
        }
    }
};