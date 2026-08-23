<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
            $table->timestamp('banned_at')->nullable()->after('role');
        });

        Schema::create('forum_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('thread_id')->nullable()->constrained('forum_threads')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained('forum_posts')->cascadeOnDelete();
            $table->string('reason', 500);
            $table->string('status')->default('open');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_reports');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'banned_at']);
        });
    }
};
