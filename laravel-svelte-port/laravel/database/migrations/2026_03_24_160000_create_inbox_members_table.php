<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbox_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['inbox_id', 'user_id'], 'index_inbox_members_on_inbox_id_and_user_id');
            $table->index('inbox_id', 'index_inbox_members_on_inbox_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_members');
    }
};
