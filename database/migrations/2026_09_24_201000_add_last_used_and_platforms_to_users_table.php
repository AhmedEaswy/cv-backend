<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_used_at')->nullable()->after('email_verified_at');
            $table->string('last_app_platform', 32)->nullable()->after('last_used_at');
            $table->json('used_platforms')->nullable()->after('last_app_platform');
            $table->boolean('uses_both_platforms')->default(false)->after('used_platforms');

            $table->index('last_used_at');
            $table->index('uses_both_platforms');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_used_at']);
            $table->dropIndex(['uses_both_platforms']);
            $table->dropColumn([
                'last_used_at',
                'last_app_platform',
                'used_platforms',
                'uses_both_platforms',
            ]);
        });
    }
};
