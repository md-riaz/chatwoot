<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Facebook Pages Table Migration
 * 
 * Facebook Pages channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_facebook_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('page_id');
            $table->text('user_access_token');
            $table->text('page_access_token');
            $table->string('instagram_id')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('page_id');
            $table->unique(['page_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_facebook_pages');
    }
};