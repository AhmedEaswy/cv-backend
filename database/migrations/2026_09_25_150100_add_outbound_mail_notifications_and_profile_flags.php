<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_contact_email')->default(true)->after('uses_both_platforms');
            $table->boolean('notify_contact_push')->default(true)->after('notify_contact_email');
        });

        Schema::table('public_profiles', function (Blueprint $table) {
            $table->boolean('enable_subdomain')->default(false)->after('enable_contact_form');
        });

        Schema::create('user_outbound_mail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('domain', 191)->nullable();
            $table->string('from_email', 191)->nullable();
            $table->string('from_name', 120)->nullable();
            $table->string('dns_verification_token', 64)->nullable();
            $table->timestamp('dns_verified_at')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_encryption', 10)->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->timestamp('smtp_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('device_push_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 512)->unique();
            $table->string('platform', 20);
            $table->string('app_version', 40)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_push_tokens');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('user_outbound_mail');

        Schema::table('public_profiles', function (Blueprint $table) {
            $table->dropColumn('enable_subdomain');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_contact_email', 'notify_contact_push']);
        });
    }
};
