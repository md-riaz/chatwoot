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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('locale')->default('en');
            $table->string('domain')->nullable();
            $table->string('support_email')->nullable();
            $table->json('settings')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->integer('status')->default(1); // 1=active, 0=inactive
            $table->timestamps();
            $table->softDeletes();

            $table->index('domain');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
