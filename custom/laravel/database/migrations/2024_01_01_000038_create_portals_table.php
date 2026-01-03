<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portals Table Migration
 * 
 * Help center portals - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('channel_web_widget_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('color')->nullable();
            $table->string('homepage_link')->nullable();
            $table->string('page_title')->nullable();
            $table->text('header_text')->nullable();
            $table->boolean('archived')->default(false);
            $table->json('config')->default('{"allowed_locales": ["en"]}');
            $table->json('ssl_settings')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('channel_web_widget_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portals');
    }
};