<?php

namespace App\Models;

use App\Casts\TranslatedText;
use App\Enums\HelpArticleKind;
use App\Models\Concerns\HasTranslatedAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HelpArticle extends Model
{
    use HasTranslatedAttributes;

    protected $fillable = [
        'kind',
        'category',
        'slug',
        'title',
        'body',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'kind' => HelpArticleKind::class,
            'title' => TranslatedText::class,
            'body' => TranslatedText::class,
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<HelpArticle>  $query
     * @return Builder<HelpArticle>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
