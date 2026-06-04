<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cover_letter_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('language')->default('en');
            $table->boolean('is_public')->default(true);
            $table->json('sections_order')->nullable();
            $table->json('info')->nullable();
            $table->json('experiences')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('device', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cover_letters');
    }
};
