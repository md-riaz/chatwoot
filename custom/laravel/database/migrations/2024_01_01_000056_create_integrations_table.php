<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('type')->comment('slack, linear, dialogflow, openai, shopify, etc.');
            $table->json('settings')->nullable();
            $table->json('credentials')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['account_id', 'type']);
            $table->index('type');
        });

        Schema::create('integrations_hooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integration_id')->nullable()->constrained('integrations')->cascadeOnDelete();
            $table->foreignId('inbox_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('app_id')->nullable();
            $table->integer('hook_type')->default(0);
            $table->integer('status')->default(1);
            $table->jsonb('settings')->default(new \Illuminate\Database\Query\Expression("'{}'::jsonb"));
            $table->string('reference_id')->nullable();
            $table->string('access_token')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'hook_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations_hooks');
        Schema::dropIfExists('integrations');
    }
};