<?php

namespace App\Http\Controllers\User\Api\V1\Products;

use App\Enums\PublicationStatus;
use App\Enums\ReactionType;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Api\V1\Review\StoreReviewRequest;
use App\Http\Resources\V1\User\Products\ProductResource;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\Product;
use App\Services\Product\ProductFacetService;
use App\Services\Product\ProductFilterService;
use App\Services\Product\ProductQueryService;
use App\Services\Review\ReviewService;
use App\Services\Search\FuzzySearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function show(Product $product)
    {
        try {
            abort_if($product->publication_status != PublicationStatus::PUBLISHED->value, Response::HTTP_NOT_FOUND);

            $userId = request()->user()?->id;

            $product->loadCount([
                'reactions as likes_count' => fn ($q) =>
                $q->where('type', ReactionType::LIKE->value),
            ]);

            if ($userId) {
                $product->load([
                    'reactions' => fn ($q) => $q
                        ->where('user_id', $userId)
                        ->select('id', 'user_id', 'reactable_id', 'reactable_type', 'type'),
                ]);
            }

            $product->loadMissing([
                'images',
                'categories',
                'brand',
                'activeVariations.variationAttributes.attribute',
                'activeVariations.variationAttributes.option',
                'reviews' => fn ($query) => $query
                    ->approved()
                    ->latest()
                    ->take(5)
                    ->with([
                        'user',
                        'messages.author',
                        'messages.business',
                        'reviewSummary'
                    ]),
            ]);

            return ApiResponse::Success('عملیات موفق', ProductResource::make($product));
        } catch (\Exception $exception) {
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR, 'خطا در عملیات');
        }
    }

    public function search(
        Request $request,
        FuzzySearchService $searchService,
        ProductQueryService $queryService,
        ProductFilterService $filterService,
        ProductFacetService $facetService
    ) {


        $userId = $request->user()?->id;

        $search = rawurldecode(trim((string) $request->get('q', '')));
        $query = $queryService->make();

        $query = $filterService->apply($query, $request);

        if ($search != '') {

            $matchedIds = array_slice(
                $searchService->search('products.index', $search, 100),
                0,
                200
            );

            if (empty($matchedIds)) {
                return ApiResponse::Success('محصولی یافت نشد', null);
            }

            $query->whereIn('id', $matchedIds);

        }


        $filters = $facetService->build(clone $query);

        $products = $query
            ->withCount([
                'reactions as likes_count' => fn ($q) =>
                $q->where('type', ReactionType::LIKE->value),
            ])
            ->when($userId, function ($query) use ($userId) {
                $query->with([
                    'reactions' => fn ($q) => $q
                        ->where('user_id', $userId)
                        ->select('id', 'user_id', 'reactable_id', 'reactable_type', 'type'),
                ]);
            })
            ->with([
                'images',
                'categories',
                'brand',
                'activeVariations.variationAttributes.attribute',
                'activeVariations.variationAttributes.option',
                'reviews' => fn ($query) => $query
                    ->approved()
                    ->latest()
                    ->take(5)
                    ->with([
                        'user',
                        'messages.author',
                        'messages.business',
                        'reviewSummary'
                    ]),
            ])
            ->paginate(15);

        return ApiResponse::Success('عملیات موفق', [
            'products' => ProductResource::collection($products),
            'filters' => $filters,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function reviews(
        Product $product,
    )
    {
        return ReviewResource::collection(
            $product->reviews()
                ->approved()
                ->with([
                    'user',
                    'messages.author',
                    'messages.business',
                    'messages.replies.author',
                    'messages.replies.business',
                ])
                ->latest()
                ->paginate()
        );
    }

}
