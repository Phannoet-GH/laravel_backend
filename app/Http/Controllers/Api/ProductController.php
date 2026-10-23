<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use App\Security\Sanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        // 1. Single product lookup via ?id= query param
        if ($request->has('id')) {
            $rawId = $request->query('id');

            // Boundary & overflow checks (e.g., negative or astronomical integer)
            if (!is_numeric($rawId) || (int) $rawId <= 0 || strlen((string) $rawId) > 10) {
                return $this->error('Product not found', 404);
            }

            return $this->show((int) $rawId);
        }

        // 2. Listing products with filters
        $category = $request->query('category');
        $search = $request->query('search');
        $limit = Sanitizer::cleanInt($request->query('limit', 50), 50);
        $offset = Sanitizer::cleanInt($request->query('offset', 0), 0);

        if ($limit <= 0 || $limit > 100) {
            $limit = 50;
        }

        $query = Product::query();

        if (!empty($category) && strtolower($category) !== 'all') {
            $cleanCategory = Sanitizer::cleanString($category, 50);
            $query->whereRaw('LOWER(category) = ?', [strtolower($cleanCategory)]);
        }

        if (!empty($search)) {
            $cleanSearch = Sanitizer::cleanString($search, 100);
            $term = '%' . strtolower($cleanSearch) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(brand) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(category) LIKE ?', [$term]);
            });
        }

        if ($request->has('in_stock')) {
            $query->where('in_stock', filter_var($request->query('in_stock'), FILTER_VALIDATE_BOOLEAN));
        }

        $products = $query->orderBy('id', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();

        return $this->success($products, [
            'count' => $products->count(),
            'category' => $category ?: 'all',
            'search' => $search,
        ]);
    }

    public function show(mixed $id): JsonResponse
    {
        if (!is_numeric($id) || (int) $id <= 0 || strlen((string) $id) > 10) {
            return $this->error('Product not found', 404);
        }

        $product = Product::with(['customerReviews' => function ($q) {
            $q->orderBy('id', 'desc');
        }])->find((int) $id);

        if (!$product) {
            return $this->error('Product not found', 404);
        }

        $data = $product->toArray();
        $data['customer_reviews'] = $product->customerReviews ? $product->customerReviews->toArray() : [];

        return $this->success($data);
    }
}
