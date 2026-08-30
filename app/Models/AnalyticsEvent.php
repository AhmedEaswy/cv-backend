<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'endpoint',
        'method',
        'ip_address',
        'country',
        'device',
        'user_agent',
        'user_id',
        'profile_id',
        'action_type',
        'channel',
        'agent_name',
        'agent_client',
        'tool_name',
        'is_agent',
        'request_data',
        'response_status',
        'duration_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'created_at' => 'datetime',
            'is_agent' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
