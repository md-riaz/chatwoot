<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Portals (Help Center)
        Schema::create('portals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('channel_web_widget_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('color')->nullable();
            $table->string('homepage_link')->nullable();
            $table->string('page_title')->nullable();
            $table->text('header_text')->nullable();
            $table->boolean('archived')->default(false);
            $table->jsonb('config')->nullable();
            $table->jsonb('ssl_settings')->nullable();
            $table->timestamps();

            $table->index('channel_web_widget_id');
        });

        // Categories
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

            $table->index('locale');
            $table->index('parent_category_id');
            $table->index('associated_category_id');
            $table->unique(['slug', 'locale', 'portal_id']);
        });

        // Folders
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('position')->nullable();
            $table->timestamps();
        });

        // Articles
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
            $table->jsonb('meta')->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('folder_id');
            $table->index('associated_article_id');
            $table->index('status');
            $table->index('views');
            $table->unique(['slug', 'portal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
        Schema::dropIfExists('folders');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('portals');
    }
};
