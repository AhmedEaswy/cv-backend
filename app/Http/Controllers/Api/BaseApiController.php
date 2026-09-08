<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BaseApiController extends Controller
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(mixed $data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'result' => $data,
        ], $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message = '', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Paginate a query when `page` is present; otherwise return the full mapped list.
     * Paginated shape: { data: [...], meta: { current_page, last_page, per_page, total, has_more } }.
     *
     * @param  callable(mixed): mixed  $mapper
     */
    protected function paginatedOrAll(Builder $query, Request $request, callable $mapper, string $message = '', int $defaultPerPage = 9): JsonResponse
    {
        if (! $request->has('page')) {
            $items = $query->get()->map($mapper)->values();

            return $this->successResponse($items, $message);
        }

        $perPage = min(50, max(1, (int) $request->input('per_page', $defaultPerPage)));
        $page = max(1, (int) $request->input('page', 1));

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->successResponse(
            $this->formatPaginator($paginator, $mapper),
            $message
        );
    }

    /**
     * @param  callable(mixed): mixed  $mapper
     * @return array{data: Collection, meta: array{current_page: int, last_page: int, per_page: int, total: int, has_more: bool}}
     */
    protected function formatPaginator(LengthAwarePaginator $paginator, callable $mapper): array
    {
        return [
            'data' => $paginator->getCollection()->map($mapper)->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ];
    }
}

