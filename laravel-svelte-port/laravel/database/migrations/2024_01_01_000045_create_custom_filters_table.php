<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom Filters Table Migration
 * 
 * User-defined filters - depends on accounts, users
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_filters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('filter_type')->default(0); // 0: conversation, 1: contact, 2: report
            $table->json('query')->nullable();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('account_id');
            $table->index('user_id');
            $table->index('filter_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_filters');
    }
};