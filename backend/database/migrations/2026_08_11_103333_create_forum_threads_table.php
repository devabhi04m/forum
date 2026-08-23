<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('forum_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('status')->default('published');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('replies_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->foreignId('last_post_id')->nullable();
            $table->timestamp('last_post_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_pinned', 'last_post_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_threads');
    }
};
