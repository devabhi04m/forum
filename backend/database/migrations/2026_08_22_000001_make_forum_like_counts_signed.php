<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// likes_count is really a vote score now, and downvotes can push it below zero
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->bigInteger('likes_count')->default(0)->change();
        });

        Schema::table('forum_posts', function (Blueprint $table) {
            $table->bigInteger('likes_count')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->unsignedBigInteger('likes_count')->default(0)->change();
        });

        Schema::table('forum_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('likes_count')->default(0)->change();
        });
    }
};
