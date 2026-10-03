<?php

namespace App\Http\Controllers\Api\Support;

use App\Enums\HelpArticleKind;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\HelpArticle;
use Illuminate\Http\Request;

class HelpArticleController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = HelpArticle::query()
            ->published()
            ->orderBy('sort_order')
            ->orderByDesc('updated_at');

        if ($kind = $request->query('kind')) {
            if (in_array($kind, HelpArticleKind::values(), true)) {
                $query->where('kind', $kind);
            }
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $locale = app()->getLocale();

        return $this->paginatedOrAll(
            $query,
            $request,
            fn (HelpArticle $article) => $this->formatArticle($article, $locale),
            __('messages.help_articles_retrieved'),
            20,
        );
    }

    public function show(Request $request, string $slug)
    {
        $article = HelpArticle::query()
            ->published()
            ->where('slug', $slug)
            ->first();

        if (! $article) {
            return $this->errorResponse(__('messages.help_article_not_found'), 404);
        }

        return $this->successResponse(
            $this->formatArticle($article, app()->getLocale(), true),
            __('messages.help_article_retrieved'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function formatArticle(HelpArticle $article, string $locale, bool $includeBody = false): array
    {
        $data = [
            'id' => $article->id,
            'kind' => $article->kind->value,
            'slug' => $article->slug,
            'category' => $article->category,
            'title' => $article->translation('title', $locale),
            'sort_order' => $article->sort_order,
            'updated_at' => $article->updated_at?->toIso8601String(),
        ];

        if ($includeBody) {
            $data['body'] = $article->translation('body', $locale);
        } else {
            $body = $article->translation('body', $locale);
            if (is_string($body)) {
                $data['excerpt'] = mb_strlen($body) > 240 ? mb_substr($body, 0, 240).'…' : $body;
            }
        }

        return $data;
    }
}
