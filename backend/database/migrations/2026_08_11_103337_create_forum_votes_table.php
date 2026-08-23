<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('thread_id')->nullable()->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained('forum_posts')->cascadeOnDelete();
            $table->smallInteger('vote');
            $table->timestamps();

            $table->unique(['user_id', 'thread_id', 'post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_votes');
    }
};
