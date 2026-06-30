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
    public function index()
    {
        try {
            $products = ProductResource::collection(
                Product::query()
                    ->with(['images', 'categories', 'brand'])
                    ->where('publication_status', PublicationStatus::PUBLISHED->value)
                    ->cursorPaginate()
            );

            return ApiResponse::Success('عملیات موفق', $products);
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }


    public function show(Product $product)
    {
        try {
            abort_if($product->publication_status != PublicationStatus::PUBLISHED->value, Response::HTTP_NOT_FOUND);

            $product->loadMissing(['images', 'categories', 'brand']);

            return ApiResponse::Success('عملیات موفق', ProductResource::make($product));
        }catch (\Exception $exception){
            report($exception);
            return ApiResponse::Fail(Response::HTTP_INTERNAL_SERVER_ERROR,'خطا در عملیات');
        }
    }

    public function search(Request $request, FuzzySearchService $searchService)
    {
        $query = trim((string) $request->get('q', ''));

        if ($query === '') {
            return ApiResponse::Success('عملیات موفق',ProductResource::collection(
                Product::query()
                    ->where('publication_status',PublicationStatus::PUBLISHED->value)
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
                    ->where('publication_status',PublicationStatus::PUBLISHED->value)
                    ->whereIn('id', $matchedIds)
                    ->orderByRaw('FIELD(id,' . implode(',', array_map('intval', $matchedIds)) . ')')
                    ->paginate(15)
            )
        );
    }


}
