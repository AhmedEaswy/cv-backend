<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('public_profile_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->boolean('is_public')->default(true);
            $table->string('language')->default('en');
            $table->string('headline')->nullable();
            $table->text('about')->nullable();
            $table->json('info')->nullable();
            $table->json('social_links')->nullable();
            $table->json('experiences')->nullable();
            $table->json('educations')->nullable();
            $table->json('projects')->nullable();
            $table->json('skills')->nullable();
            $table->json('languages')->nullable();
            $table->json('services')->nullable();
            $table->json('testimonials')->nullable();
            $table->json('certifications')->nullable();
            $table->json('achievements')->nullable();
            $table->json('availability')->nullable();
            $table->json('cta')->nullable();
            $table->json('sections_order')->nullable();
            $table->json('seo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_profiles');
    }
};
