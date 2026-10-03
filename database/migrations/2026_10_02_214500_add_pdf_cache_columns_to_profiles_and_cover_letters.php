<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
            $table->string('pdf_fingerprint', 64)->nullable();
        });

        Schema::table('cover_letters', function (Blueprint $table) {
            $table->string('pdf_path')->nullable();
            $table->string('pdf_fingerprint', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'pdf_fingerprint']);
        });

        Schema::table('cover_letters', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'pdf_fingerprint']);
        });
    }
};
