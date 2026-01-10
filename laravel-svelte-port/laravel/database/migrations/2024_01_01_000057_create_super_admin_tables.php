<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Platform Apps table
        Schema::create('platform_apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Platform App Permissibles (polymorphic)
        Schema::create('platform_app_permissibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_app_id')->constrained()->cascadeOnDelete();
            $table->morphs('permissible');
            $table->timestamps();

            $table->unique(['platform_app_id', 'permissible_type', 'permissible_id'], 'platform_app_permissibles_unique');
        });

        // Installation Configs table
        Schema::create('installation_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('serialized_value');
            $table->boolean('locked')->default(true);
            $table->string('display_title')->nullable();
            $table->text('description')->nullable();
            $table->string('type', 20)->default('text');
            $table->json('options')->nullable();
            $table->timestamps();

            $table->index(['name', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_app_permissibles');
        Schema::dropIfExists('platform_apps');
        Schema::dropIfExists('installation_configs');
    }
};