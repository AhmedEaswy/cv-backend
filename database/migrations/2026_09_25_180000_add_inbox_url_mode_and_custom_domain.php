<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_profiles', function (Blueprint $table) {
            $table->boolean('enable_inbox')->default(true)->after('enable_contact_form');
            $table->string('profile_url_mode', 32)->default('slug')->after('enable_subdomain');
            $table->string('custom_domain', 191)->nullable()->after('profile_url_mode');
            $table->string('custom_domain_dns_token', 64)->nullable()->after('custom_domain');
            $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain_dns_token');
        });

        DB::table('public_profiles')
            ->where('enable_subdomain', true)
            ->update(['profile_url_mode' => 'subdomain']);
    }

    public function down(): void
    {
        Schema::table('public_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'enable_inbox',
                'profile_url_mode',
                'custom_domain',
                'custom_domain_dns_token',
                'custom_domain_verified_at',
            ]);
        });
    }
};
