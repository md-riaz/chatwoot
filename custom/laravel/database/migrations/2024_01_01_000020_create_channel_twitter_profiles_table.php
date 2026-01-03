<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Twitter Profiles Table Migration
 * 
 * Twitter profiles channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_twitter_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('profile_id');
            $table->text('twitter_access_token');
            $table->text('twitter_access_token_secret');
            $table->boolean('tweets_enabled')->default(true);
            $table->timestamps();

            $table->index('account_id');
            $table->unique(['account_id', 'profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_twitter_profiles');
    }
};