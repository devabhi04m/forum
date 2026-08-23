<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // lets the dummy data tool tell its own categories apart from real ones
    public function up(): void
    {
        Schema::table('forum_categories', function (Blueprint $table) {
            $table->boolean('is_dummy')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('forum_categories', function (Blueprint $table) {
            $table->dropColumn('is_dummy');
        });
    }
};
