<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('forum_posts')->nullOnDelete();
            $table->longText('content');
            $table->boolean('is_solution')->default(false);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['thread_id', 'created_at']);
        });

        Schema::table('forum_threads', function (Blueprint $table) {
            $table->foreign('last_post_id')->references('id')->on('forum_posts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->dropForeign(['last_post_id']);
        });

        Schema::dropIfExists('forum_posts');
    }
};
