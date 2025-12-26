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

        Schema::create('integration_hooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integration_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('app_id')->nullable();
            $table->string('hook_type')->default('account');
            $table->string('status')->default('enabled');
            $table->json('settings')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'hook_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_hooks');
        Schema::dropIfExists('integrations');
    }
};
