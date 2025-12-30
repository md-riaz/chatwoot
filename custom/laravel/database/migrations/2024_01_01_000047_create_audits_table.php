<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('associated_id')->nullable();
            $table->string('associated_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->nullable();
            $table->string('username')->nullable();
            $table->string('action')->nullable();
            $table->jsonb('audited_changes')->nullable();
            $table->integer('version')->default(0);
            $table->string('comment')->nullable();
            $table->string('remote_address')->nullable();
            $table->string('request_uuid')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['associated_type', 'associated_id'], 'audits_associated_index');
            $table->index(['auditable_type', 'auditable_id', 'version'], 'audits_auditable_index');
            $table->index('created_at');
            $table->index('request_uuid');
            $table->index(['user_id', 'user_type'], 'audits_user_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
