<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_profiles', function (Blueprint $table) {
            $table->boolean('enable_contact_form')->default(false)->after('is_public');
            $table->string('contact_form_recipient')->nullable()->after('enable_contact_form');
        });
    }

    public function down(): void
    {
        Schema::table('public_profiles', function (Blueprint $table) {
            $table->dropColumn(['enable_contact_form', 'contact_form_recipient']);
        });
    }
};
