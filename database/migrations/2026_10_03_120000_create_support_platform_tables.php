<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16);
            $table->string('category', 120)->nullable();
            $table->string('slug', 191)->unique();
            $table->json('title');
            $table->json('body');
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['kind', 'is_published', 'sort_order']);
            $table->index(['category', 'is_published']);
        });

        Schema::create('product_tours', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_tour_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_tour_id')->constrained('product_tours')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('title');
            $table->json('body');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['product_tour_id', 'sort_order']);
        });

        Schema::create('user_tour_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_tour_id')->constrained('product_tours')->cascadeOnDelete();
            $table->string('status', 20);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'product_tour_id']);
        });

        Schema::create('feature_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('body');
            $table->string('status', 32)->default('under_review');
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('vote_count')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'vote_count']);
            $table->index(['status', 'is_published']);
        });

        Schema::create('feature_request_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_request_id')->constrained('feature_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['feature_request_id', 'user_id']);
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->string('email', 191);
            $table->string('subject', 200);
            $table->text('body');
            $table->string('status', 32)->default('open');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1000)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('email');
        });

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('moderation_status', 32)->default('approved')->after('is_spam')->index();
            $table->timestamp('delivered_at')->nullable()->after('moderation_status');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'delivered_at']);
        });

        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('feature_request_votes');
        Schema::dropIfExists('feature_requests');
        Schema::dropIfExists('user_tour_progress');
        Schema::dropIfExists('product_tour_steps');
        Schema::dropIfExists('product_tours');
        Schema::dropIfExists('help_articles');
    }
};
