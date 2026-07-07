<?php

namespace App\Http\Controllers\User\Api\V1\Products;

use App\Enums\PublicationStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\Products\ProductResource;
use App\Models\Product;
use App\Services\Search\FuzzySearchService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{

    public function show(Product $product)
    {
        try {
            abort_if($product->publication_status != PublicationStatus::PUBLISHED->value, Response::HTTP_NOT_FOUND);

            $product->loadMissing([
                'images',
                'categories',
                'brand',
                'activeVariations.variationAttributes.attribute',
                'activeVariations.variationAttributes.option',
            ]);

            return ApiResponse::Success('عملیات موفق', ProductResource::make($product));
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    public function search(Request $request, FuzzySearchService $searchService)
    {
        $query = rawurldecode(trim((string) $request->get('q', '')));

        if ($query === '') {
            return ApiResponse::Success('عملیات موفق',ProductResource::collection(
                Product::query()
                    ->when(request()->filled('category_slug'),
                        fn($q) => $q->whereHas('categories', function ($q) {
                            $q->where('categories.slug', request()->category_slug);
                        }
                        ))
                    ->where('publication_status',PublicationStatus::PUBLISHED->value)
                    ->with([
                        'images',
                        'categories',
                        'brand',
                        'activeVariations.variationAttributes.attribute',
                        'activeVariations.variationAttributes.option',
                    ])
                    ->paginate(15)
            ));
        }

        $matchedIds = array_slice($searchService->search('products.index', $query, 100), 0, 200);

        if (empty($matchedIds)) {
            return ApiResponse::Success('محصولی یافت نشد',null);
        }

        return ApiResponse::Success('عملیات موفق',
            ProductResource::collection(
                Product::query()
                    ->when(request()->filled('category_slug'),
                        fn($q) => $q->whereHas('categories', function ($q) {
                            $q->where('categories.slug', request()->category_slug);
                        }
                        ))
                    ->where('publication_status',PublicationStatus::PUBLISHED->value)
                    ->whereIn('id', $matchedIds)
                    ->orderByRaw('FIELD(id,' . implode(',', array_map('intval', $matchedIds)) . ')')
                    ->with([
                        'images',
                        'categories',
                        'brand',
                        'activeVariations.variationAttributes.attribute',
                        'activeVariations.variationAttributes.option',
                    ])
                    ->paginate(15)
            )
        );
    }


}
