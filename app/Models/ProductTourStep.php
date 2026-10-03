<?php

namespace App\Models;

use App\Casts\TranslatedText;
use App\Models\Concerns\HasTranslatedAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTourStep extends Model
{
    use HasTranslatedAttributes;

    protected $fillable = [
        'product_tour_id',
        'sort_order',
        'title',
        'body',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'title' => TranslatedText::class,
            'body' => TranslatedText::class,
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(ProductTour::class, 'product_tour_id');
    }
}
