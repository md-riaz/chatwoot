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
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->default('email')->after('password');
            $table->string('uid')->nullable()->after('provider');
            $table->string('sso_auth_token')->nullable()->after('uid');
            
            $table->index('provider');
            $table->index('uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['provider']);
            $table->dropIndex(['uid']);
            $table->dropColumn(['provider', 'uid', 'sso_auth_token']);
        });
    }
};