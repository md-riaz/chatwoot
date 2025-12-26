<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->unsignedBigInteger('inbox_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->float('value');
            $table->float('value_in_business_hours')->nullable();
            $table->timestamp('event_start_time')->nullable();
            $table->timestamp('event_end_time')->nullable();
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('inbox_id');
            $table->index('user_id');
            $table->index('name');
            $table->index(['account_id', 'name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_events');
    }
};
