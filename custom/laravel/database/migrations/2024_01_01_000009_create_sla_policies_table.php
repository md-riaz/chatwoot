<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('first_response_time_threshold')->nullable()->comment('In seconds');
            $table->integer('next_response_time_threshold')->nullable()->comment('In seconds');
            $table->integer('resolution_time_threshold')->nullable()->comment('In seconds');
            $table->boolean('only_during_business_hours')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['account_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
