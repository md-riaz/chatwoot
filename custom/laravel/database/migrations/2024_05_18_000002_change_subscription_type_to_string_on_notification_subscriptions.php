<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_subscriptions', function (Blueprint $table) {
            $table->string('subscription_type_new')->nullable()->after('user_id');
        });

        $mapping = [
            1 => 'fcm',
            2 => 'browser_push',
        ];

        foreach ($mapping as $intValue => $stringValue) {
            DB::table('notification_subscriptions')
                ->where('subscription_type', $intValue)
                ->update(['subscription_type_new' => $stringValue]);
        }

        DB::table('notification_subscriptions')
            ->whereNull('subscription_type_new')
            ->update(['subscription_type_new' => 'fcm']);

        Schema::table('notification_subscriptions', function (Blueprint $table) {
            $table->dropColumn('subscription_type');
        });

        Schema::table('notification_subscriptions', function (Blueprint $table) {
            $table->renameColumn('subscription_type_new', 'subscription_type');
        });
    }

    public function down(): void
    {
        Schema::table('notification_subscriptions', function (Blueprint $table) {
            $table->unsignedTinyInteger('subscription_type_int')->nullable()->after('user_id');
        });

        $reverseMapping = [
            'fcm' => 1,
            'browser_push' => 2,
        ];

        foreach ($reverseMapping as $stringValue => $intValue) {
            DB::table('notification_subscriptions')
                ->where('subscription_type', $stringValue)
                ->update(['subscription_type_int' => $intValue]);
        }

        DB::table('notification_subscriptions')
            ->whereNull('subscription_type_int')
            ->update(['subscription_type_int' => 1]);

        Schema::table('notification_subscriptions', function (Blueprint $table) {
            $table->dropColumn('subscription_type');
        });

        Schema::table('notification_subscriptions', function (Blueprint $table) {
            $table->renameColumn('subscription_type_int', 'subscription_type');
        });
    }
};
