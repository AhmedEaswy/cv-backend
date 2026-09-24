<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anonymous_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('device', 255)->nullable();
            $table->timestamps();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->uuid('anonymous_user_id')->nullable()->after('user_id');
            $table->string('client_ref', 64)->nullable()->after('anonymous_user_id');

            $table->foreign('anonymous_user_id')
                ->references('id')
                ->on('anonymous_users')
                ->nullOnDelete();

            $table->unique(['anonymous_user_id', 'client_ref'], 'profiles_anonymous_client_ref_unique');
            $table->index('anonymous_user_id');
        });

        Schema::table('cover_letters', function (Blueprint $table) {
            $table->uuid('anonymous_user_id')->nullable()->after('user_id');
            $table->string('client_ref', 64)->nullable()->after('anonymous_user_id');

            $table->foreign('anonymous_user_id')
                ->references('id')
                ->on('anonymous_users')
                ->nullOnDelete();

            $table->unique(['anonymous_user_id', 'client_ref'], 'cover_letters_anonymous_client_ref_unique');
            $table->index('anonymous_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('cover_letters', function (Blueprint $table) {
            $table->dropUnique('cover_letters_anonymous_client_ref_unique');
            $table->dropForeign(['anonymous_user_id']);
            $table->dropColumn(['anonymous_user_id', 'client_ref']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropUnique('profiles_anonymous_client_ref_unique');
            $table->dropForeign(['anonymous_user_id']);
            $table->dropColumn(['anonymous_user_id', 'client_ref']);
        });

        Schema::dropIfExists('anonymous_users');
    }
};
