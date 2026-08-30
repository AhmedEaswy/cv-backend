<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageDaily extends Model
{
    protected $fillable = [
        'date',
        'channel',
        'agent_client',
        'tool_name',
        'calls',
        'unique_ips',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'calls' => 'integer',
            'unique_ips' => 'integer',
        ];
    }
}
