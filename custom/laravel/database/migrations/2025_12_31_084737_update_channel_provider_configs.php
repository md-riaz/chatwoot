<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_whatsapp', function (Blueprint $table) {
            $table->string('phone_number_id')->nullable()->after('phone_number');
            $table->string('business_account_id')->nullable()->after('phone_number_id');
            $table->text('access_token')->nullable()->after('business_account_id');
            $table->string('verify_token')->nullable()->after('access_token');
            $table->jsonb('provider_config')->default(new \Illuminate\Database\Query\Expression("'{}'::jsonb"))->change();
        });

        Schema::table('channel_twitter_profiles', function (Blueprint $table) {
            $table->jsonb('provider_config')->default(new \Illuminate\Database\Query\Expression("'{}'::jsonb"))->after('tweets_enabled');
        });

        Schema::table('channel_voice', function (Blueprint $table) {
            $table->jsonb('provider_config')->default(new \Illuminate\Database\Query\Expression("'{}'::jsonb"))->change();
        });
    }

    public function down(): void
    {
        Schema::table('channel_whatsapp', function (Blueprint $table) {
            $table->dropColumn(['phone_number_id', 'business_account_id', 'access_token', 'verify_token']);
            $table->jsonb('provider_config')->nullable()->change();
        });

        Schema::table('channel_twitter_profiles', function (Blueprint $table) {
            $table->dropColumn('provider_config');
        });

        Schema::table('channel_voice', function (Blueprint $table) {
            $table->json('provider_config')->change();
        });
    }
};
