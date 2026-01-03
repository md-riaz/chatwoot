<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('import_type')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending');
            $table->integer('total_rows')->nullable();
            $table->integer('processed_rows')->default(0);
            $table->jsonb('meta')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_imports');
    }
};