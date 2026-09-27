<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_spam_blocklist', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('reason', 32);
            $table->foreignId('source_message_id')->nullable()->constrained('contact_messages')->nullOnDelete();
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable()->after('read_at');
            $table->boolean('is_spam')->default(false)->after('hidden_at');
        });

        Schema::create('contact_message_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_message_id')->constrained('contact_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->string('from_email', 191)->nullable();
            $table->string('from_name', 120)->nullable();
            $table->string('mail_mode', 20)->default('company');
            $table->string('delivery_status', 20)->default('pending')->index();
            $table->string('provider_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_message_replies');

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['hidden_at', 'is_spam']);
        });

        Schema::dropIfExists('contact_spam_blocklist');
    }
};
