<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __construct(
        protected GlobalSearchService $searchService
    ) {}

    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->get('q', '');
        $results = $this->searchService->search($query);

        return response()->json([
            'query' => $query,
            'results' => $results,
            'count' => array_reduce($results, fn ($carry, $items) => $carry + count($items), 0),
        ]);
    }
}
