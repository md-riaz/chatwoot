<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_storage_blobs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('filename');
            $table->string('content_type')->nullable();
            $table->text('metadata')->nullable();
            $table->unsignedBigInteger('byte_size');
            $table->string('checksum')->nullable();
            $table->string('service_name');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('active_storage_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('record_type');
            $table->unsignedBigInteger('record_id');
            $table->foreignId('blob_id')->constrained('active_storage_blobs')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['record_type', 'record_id', 'name', 'blob_id'],
                'active_storage_attachments_uniqueness'
            );
        });

        Schema::create('active_storage_variant_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blob_id')->constrained('active_storage_blobs')->cascadeOnDelete();
            $table->string('variation_digest');

            $table->unique(['blob_id', 'variation_digest'], 'active_storage_variant_records_uniqueness');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_storage_variant_records');
        Schema::dropIfExists('active_storage_attachments');
        Schema::dropIfExists('active_storage_blobs');
    }
};
