<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('filter_type')->default(0); // 0: conversation, 1: contact, 2: report
            $table->jsonb('query');
            $table->timestamps();

            $table->index('filter_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_filters');
    }
};
