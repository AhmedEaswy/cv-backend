<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->string('preview_ar')->nullable()->after('preview');
        });

        Schema::table('cover_letter_templates', function (Blueprint $table) {
            $table->string('preview_ar')->nullable()->after('preview');
        });

        Schema::table('public_profile_templates', function (Blueprint $table) {
            $table->string('preview_ar')->nullable()->after('preview');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('preview_ar');
        });

        Schema::table('cover_letter_templates', function (Blueprint $table) {
            $table->dropColumn('preview_ar');
        });

        Schema::table('public_profile_templates', function (Blueprint $table) {
            $table->dropColumn('preview_ar');
        });
    }
};
