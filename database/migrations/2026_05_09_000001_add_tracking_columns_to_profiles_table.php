<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('deleted_at');
            $table->string('country', 100)->nullable()->after('ip_address');
            $table->string('device', 255)->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'country', 'device']);
        });
    }
};
