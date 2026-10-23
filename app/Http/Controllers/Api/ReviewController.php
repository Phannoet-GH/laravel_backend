<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use App\Models\Review;
use App\Security\Sanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $productId = Sanitizer::cleanInt($request->query('product_id', 0));

        if ($productId <= 0) {
            return $this->error('Valid product_id is required.', 400);
        }

        $reviews = Review::where('product_id', $productId)
            ->orderBy('id', 'desc')
            ->get();

        return $this->success($reviews);
    }

    public function store(Request $request): JsonResponse
    {
        $productId = Sanitizer::cleanInt($request->input('product_id', 0));
        $userName = Sanitizer::cleanString($request->input('user_name', 'Emma Wills'), 100);
        $rawRating = Sanitizer::cleanFloat($request->input('rating', 5.0));
        $rating = min(5.0, max(1.0, $rawRating));
        $comment = Sanitizer::cleanString($request->input('comment', ''), 1000);

        if ($productId <= 0 || empty($comment)) {
            return $this->error('Product ID and comment are required.', 422);
        }

        $review = Review::create([
            'product_id' => $productId,
            'user_name' => $userName,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        // Recalculate and update parent product's rating and reviews_count
        $product = Product::find($productId);
        if ($product) {
            $avgRating = Review::where('product_id', $productId)->avg('rating');
            $reviewsCount = Review::where('product_id', $productId)->count();
            $product->update([
                'rating' => round($avgRating, 1),
                'reviews_count' => $reviewsCount,
            ]);
        }

        return $this->success($review, [], 201);
    }
}
