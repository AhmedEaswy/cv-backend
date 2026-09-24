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
        'device_type',
        'os',
        'os_version',
        'browser',
        'device_model',
        'app_platform',
        'app_version',
        'locale',
        'user_agent',
        'user_id',
        'anonymous_user_id',
        'profile_id',
        'action_type',
        'channel',
        'agent_name',
        'agent_client',
        'tool_name',
        'is_agent',
        'request_data',
        'meta',
        'response_status',
        'duration_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'meta' => 'array',
            'created_at' => 'datetime',
            'is_agent' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anonymousUser(): BelongsTo
    {
        return $this->belongsTo(AnonymousUser::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
