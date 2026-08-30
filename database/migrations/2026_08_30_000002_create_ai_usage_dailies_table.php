<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_dailies', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('channel', 20);
            $table->string('agent_client', 80)->nullable();
            $table->string('tool_name', 120)->nullable();
            $table->unsignedInteger('calls')->default(0);
            $table->unsignedInteger('unique_ips')->default(0);
            $table->timestamps();

            $table->unique(['date', 'channel', 'agent_client', 'tool_name'], 'ai_usage_dailies_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_dailies');
    }
};
