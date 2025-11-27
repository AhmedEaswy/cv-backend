<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['user_id']);

            // Make user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Add new columns
            $table->string('name')->after('user_id');
            $table->string('language', 10)->default('en')->after('name');
            $table->json('sections_order')->nullable()->after('language');

            // Re-add foreign key constraint with nullable support
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['user_id']);

            // Remove added columns
            $table->dropColumn(['name', 'language', 'sections_order']);

            // Restore user_id to not nullable
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // Re-add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
