<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbox_assignment_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbox_id')->constrained()->onDelete('cascade');
            $table->foreignId('assignment_policy_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique('inbox_id');
            $table->index('assignment_policy_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_assignment_policies');
    }
};