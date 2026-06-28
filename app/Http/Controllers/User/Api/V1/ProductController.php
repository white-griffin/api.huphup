<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\PublicationStatus;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\Products\ProductResource;
use App\Models\Product;
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
}
