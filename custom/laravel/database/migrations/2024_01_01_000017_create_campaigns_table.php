<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('display_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('message');
            $table->integer('campaign_type')->default(0); // 0: ongoing, 1: one_off
            $table->integer('campaign_status')->default(0); // 0: active, 1: completed
            $table->boolean('enabled')->default(true);
            $table->boolean('trigger_only_during_business_hours')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->jsonb('trigger_rules')->nullable();
            $table->jsonb('audience')->nullable();
            $table->jsonb('template_params')->nullable();
            $table->timestamps();

            $table->index('campaign_type');
            $table->index('campaign_status');
            $table->index('scheduled_at');
            $table->unique(['account_id', 'display_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
