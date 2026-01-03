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
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('inbox_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->decimal('value', 10, 2);
            $table->decimal('value_in_business_hours', 10, 2)->nullable();
            $table->timestamp('event_start_time')->nullable();
            $table->timestamp('event_end_time')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'name', 'created_at']);
            $table->index('conversation_id');
            $table->index('inbox_id');
            $table->index('user_id');
            $table->index('name');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_events');
    }
};