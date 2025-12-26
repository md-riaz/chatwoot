<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->integer('day_of_week'); // 0-6 (Sunday-Saturday)
            $table->integer('open_hour')->nullable();
            $table->integer('open_minutes')->nullable();
            $table->integer('close_hour')->nullable();
            $table->integer('close_minutes')->nullable();
            $table->boolean('open_all_day')->default(false);
            $table->boolean('closed_all_day')->default(false);
            $table->timestamps();

            $table->index('day_of_week');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
