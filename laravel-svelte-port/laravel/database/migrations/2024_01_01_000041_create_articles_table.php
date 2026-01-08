<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Articles Table Migration
 * 
 * Help center articles - depends on accounts, portals, categories, folders, users
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portal_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('associated_article_id')->nullable();
            $table->string('title')->nullable();
            $table->string('slug');
            $table->text('content')->nullable();
            $table->text('description')->nullable();
            $table->integer('status')->default(0); // 0: draft, 1: published, 2: archived
            $table->integer('position')->nullable();
            $table->integer('views')->default(0);
            $table->string('locale')->default('en');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('portal_id');
            $table->index('category_id');
            $table->index('folder_id');
            $table->index('author_id');
            $table->index('associated_article_id');
            $table->index('status');
            $table->index('views');
            $table->unique('slug');
            
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
            $table->foreign('associated_article_id')->references('id')->on('articles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};