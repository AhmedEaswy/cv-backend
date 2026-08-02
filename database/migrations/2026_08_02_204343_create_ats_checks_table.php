<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ats_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 20); // structured | pdf
            $table->unsignedTinyInteger('score');
            $table->string('grade', 1);
            $table->string('language', 10)->nullable();
            $table->boolean('has_job_description')->default(false);
            $table->unsignedTinyInteger('keyword_coverage')->nullable();
            $table->json('categories')->nullable();
            $table->json('checks')->nullable();
            $table->json('keywords')->nullable();
            $table->text('job_description')->nullable();
            $table->string('candidate_name')->nullable();
            $table->string('candidate_email')->nullable();
            $table->string('pdf_original_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('device')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['score', 'created_at']);
            $table->index(['grade', 'created_at']);
            $table->index(['source', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ats_checks');
    }
};
