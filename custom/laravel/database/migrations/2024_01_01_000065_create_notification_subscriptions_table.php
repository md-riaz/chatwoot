<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('subscription_type');
            $table->jsonb('subscription_attributes')->default(new Expression("'{}'::jsonb"));
            $table->timestamps();
            $table->string('identifier')->nullable();

            $table->unique('identifier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_subscriptions');
    }
};