<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Companies Table Migration
 * 
 * Business entities - depends on accounts
 * Must be created before contacts (contacts reference companies)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->text('description')->nullable();
            $table->integer('contacts_count')->default(0);
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'account_id']);
            $table->unique(['domain', 'account_id'], 'companies_domain_account_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};