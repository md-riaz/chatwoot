<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('macros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->integer('visibility')->default(0); // 0: personal, 1: global
            $table->jsonb('actions');
            $table->timestamps();

            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('macros');
    }
};
