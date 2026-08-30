<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('channel', 20)->nullable()->after('action_type');
            $table->string('agent_name', 80)->nullable()->after('channel');
            $table->string('agent_client', 80)->nullable()->after('agent_name');
            $table->string('tool_name', 120)->nullable()->after('agent_client');
            $table->boolean('is_agent')->default(false)->after('tool_name');

            $table->index('created_at');
            $table->index(['action_type', 'created_at']);
            $table->index(['channel', 'created_at']);
            $table->index(['is_agent', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['action_type', 'created_at']);
            $table->dropIndex(['channel', 'created_at']);
            $table->dropIndex(['is_agent', 'created_at']);
            $table->dropColumn(['channel', 'agent_name', 'agent_client', 'tool_name', 'is_agent']);
        });
    }
};
