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
        Schema::table('applied_slas', function (Blueprint $table) {
            $table->integer('sla_status')->default(0)->after('conversation_id');
            
            $table->index('sla_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applied_slas', function (Blueprint $table) {
            $table->dropIndex(['sla_status']);
            $table->dropColumn('sla_status');
        });
    }
};