<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_hooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('inbox_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('app_id');
            $table->text('access_token')->nullable();
            $table->string('reference_id')->nullable();
            $table->json('settings')->nullable();
            $table->string('status')->default('enabled');
            $table->string('hook_type')->default('account');
            $table->timestamps();

            $table->index(['account_id', 'app_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_hooks');
    }
};