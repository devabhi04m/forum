<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_thread_tag', function (Blueprint $table) {
            $table->foreignId('thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('forum_tags')->cascadeOnDelete();

            $table->primary(['thread_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_thread_tag');
    }
};
