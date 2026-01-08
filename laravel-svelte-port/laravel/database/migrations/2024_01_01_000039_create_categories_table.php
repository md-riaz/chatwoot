<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categories Table Migration
 * 
 * Help center categories - depends on accounts, portals
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portal_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('parent_category_id')->nullable();
            $table->unsignedBigInteger('associated_category_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->default('');
            $table->integer('position')->nullable();
            $table->string('locale')->default('en');
            $table->timestamps();

            $table->index('account_id');
            $table->index('portal_id');
            $table->index('locale');
            $table->index('parent_category_id');
            $table->index('associated_category_id');
            $table->unique(['slug', 'locale', 'portal_id']);
            
            $table->foreign('parent_category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('associated_category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};