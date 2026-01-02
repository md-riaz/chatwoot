<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('domain')->nullable();
            $table->text('description')->nullable();
            $table->text('avatar_url')->nullable();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->integer('contacts_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes matching Rails
            $table->index(['name', 'account_id']);
            $table->index(['account_id']);
        });

        // Add unique constraint for domain per account (only when domain is not null)
        Schema::table('companies', function (Blueprint $table) {
            $table->unique(['account_id', 'domain'], 'companies_account_domain_unique');
        });

        // Add company_id to contacts table if it doesn't exist
        if (!Schema::hasColumn('contacts', 'company_id')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contacts', 'company_id')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }

        Schema::dropIfExists('companies');
    }
};
