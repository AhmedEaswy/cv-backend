<?php

namespace App\Models;

use App\Enums\UserTourProgressStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTourProgress extends Model
{
    protected $table = 'user_tour_progress';

    protected $fillable = [
        'user_id',
        'product_tour_id',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => UserTourProgressStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(ProductTour::class, 'product_tour_id');
    }
}
