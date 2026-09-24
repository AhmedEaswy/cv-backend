<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('device_type', 32)->nullable()->after('device');
            $table->string('os', 64)->nullable()->after('device_type');
            $table->string('os_version', 64)->nullable()->after('os');
            $table->string('browser', 64)->nullable()->after('os_version');
            $table->string('device_model', 128)->nullable()->after('browser');
            $table->string('app_platform', 32)->nullable()->after('device_model');
            $table->string('app_version', 64)->nullable()->after('app_platform');
            $table->string('locale', 32)->nullable()->after('app_version');
            $table->uuid('anonymous_user_id')->nullable()->after('user_id');
            $table->json('meta')->nullable()->after('request_data');

            $table->foreign('anonymous_user_id')
                ->references('id')
                ->on('anonymous_users')
                ->nullOnDelete();

            $table->index(['action_type', 'created_at'], 'analytics_events_action_created_index');
            $table->index(['app_platform', 'created_at'], 'analytics_events_platform_created_index');
            $table->index(['anonymous_user_id', 'created_at'], 'analytics_events_anonymous_created_index');
            $table->index(['os', 'created_at'], 'analytics_events_os_created_index');
        });

        Schema::table('anonymous_users', function (Blueprint $table) {
            $table->string('device_type', 32)->nullable()->after('device');
            $table->string('os', 64)->nullable()->after('device_type');
            $table->string('os_version', 64)->nullable()->after('os');
            $table->string('browser', 64)->nullable()->after('os_version');
            $table->string('device_model', 128)->nullable()->after('browser');
            $table->string('app_platform', 32)->nullable()->after('device_model');
            $table->string('app_version', 64)->nullable()->after('app_platform');
            $table->string('locale', 32)->nullable()->after('app_version');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex('analytics_events_action_created_index');
            $table->dropIndex('analytics_events_platform_created_index');
            $table->dropIndex('analytics_events_anonymous_created_index');
            $table->dropIndex('analytics_events_os_created_index');
            $table->dropForeign(['anonymous_user_id']);
            $table->dropColumn([
                'device_type',
                'os',
                'os_version',
                'browser',
                'device_model',
                'app_platform',
                'app_version',
                'locale',
                'anonymous_user_id',
                'meta',
            ]);
        });

        Schema::table('anonymous_users', function (Blueprint $table) {
            $table->dropColumn([
                'device_type',
                'os',
                'os_version',
                'browser',
                'device_model',
                'app_platform',
                'app_version',
                'locale',
            ]);
        });
    }
};
