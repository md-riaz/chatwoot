<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portals_members', function (Blueprint $table) {
            $table->foreignId('portal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unique(['portal_id', 'user_id']);
            $table->index('portal_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portals_members');
    }
};
