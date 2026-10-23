<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Wishlist;
use App\Security\Sanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $userId = Sanitizer::cleanString($request->query('user_id', 'guest'), 60);

        $wishlists = Wishlist::with('products')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere('user_id', 'default');
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        $formatted = $wishlists->map(fn($w) => $this->formatWishlist($w));

        return $this->success($formatted);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = Sanitizer::cleanString($request->input('user_id', 'guest'), 60);
        $id = $request->filled('id')
            ? Sanitizer::cleanString($request->input('id'), 60)
            : ('wish_' . time() . '_' . bin2hex(random_bytes(3)));

        $wishlist = Wishlist::create([
            'id' => $id,
            'user_id' => $userId,
            'name' => Sanitizer::cleanString($request->input('name', 'My Wishlist'), 150),
            'description' => Sanitizer::cleanString($request->input('description', ''), 255),
            'icon' => Sanitizer::cleanString($request->input('icon', '✨'), 20),
            'is_default' => filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN),
        ]);

        $productIds = $request->input('product_ids', []);
        if (is_array($productIds) && !empty($productIds)) {
            $wishlist->products()->syncWithoutDetaching($productIds);
        }

        $wishlist->load('products');
        return $this->success($this->formatWishlist($wishlist), [], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $id = Sanitizer::cleanString($request->input('id') ?: $request->query('id'), 60);
        $userId = $request->has('user_id') ? Sanitizer::cleanString($request->input('user_id') ?: $request->query('user_id'), 60) : null;

        if (empty($id)) {
            return $this->error('Wishlist ID is required.', 400);
        }

        $query = Wishlist::where('id', $id);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $query->delete();

        return $this->success(['success' => true, 'message' => 'Wishlist deleted.']);
    }

    public function addItem(Request $request): JsonResponse
    {
        $wishlistId = Sanitizer::cleanString($request->input('wishlist_id', ''), 60);
        $productId = Sanitizer::cleanInt($request->input('product_id', 0));

        if (empty($wishlistId) || $productId <= 0) {
            return $this->error('Wishlist ID and valid Product ID are required.', 400);
        }

        $wishlist = Wishlist::find($wishlistId);
        if (!$wishlist) {
            return $this->error('Wishlist not found.', 404);
        }

        $wishlist->products()->syncWithoutDetaching([$productId]);

        return $this->success(['success' => true, 'message' => 'Item added to wishlist.']);
    }

    public function removeItem(Request $request): JsonResponse
    {
        $wishlistId = Sanitizer::cleanString($request->input('wishlist_id') ?: $request->query('wishlist_id'), 60);
        $productId = Sanitizer::cleanInt($request->input('product_id') ?: $request->query('product_id', 0));

        if (empty($wishlistId) || $productId <= 0) {
            return $this->error('Wishlist ID and valid Product ID are required.', 400);
        }

        $wishlist = Wishlist::find($wishlistId);
        if ($wishlist) {
            $wishlist->products()->detach($productId);
        }

        return $this->success(['success' => true, 'message' => 'Item removed from wishlist.']);
    }

    public function favorites(Request $request): JsonResponse
    {
        $userId = Sanitizer::cleanString($request->query('user_id', 'guest'), 60);

        $wishlist = Wishlist::with('products')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere('user_id', 'default');
            })
            ->where(function ($q) {
                $q->where('is_default', true)
                    ->orWhere('name', 'Favorites')
                    ->orWhere('name', 'My Wishlist');
            })
            ->first();

        $productIds = $wishlist
            ? $wishlist->products->pluck('id')->map(fn($id) => (int) $id)->values()->toArray()
            : [];

        return $this->success($productIds);
    }

    public function toggleFavorite(Request $request): JsonResponse
    {
        $userId = Sanitizer::cleanString($request->input('user_id', 'guest'), 60);
        $productId = Sanitizer::cleanInt($request->input('product_id', 0));

        if ($productId <= 0) {
            return $this->error('Valid Product ID is required.', 400);
        }

        $wishlist = Wishlist::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('is_default', true)
                    ->orWhere('name', 'Favorites')
                    ->orWhere('name', 'My Wishlist');
            })
            ->first();

        if (!$wishlist) {
            $wishlist = Wishlist::create([
                'id' => 'wish_' . time() . '_' . bin2hex(random_bytes(3)),
                'user_id' => $userId,
                'name' => 'Favorites',
                'description' => 'Favorite items',
                'icon' => '❤️',
                'is_default' => true,
            ]);
        }

        $hasItem = DB::table('wishlist_items')
            ->where('wishlist_id', $wishlist->id)
            ->where('product_id', $productId)
            ->exists();

        if ($hasItem) {
            $wishlist->products()->detach($productId);
            return $this->success([
                'success' => true,
                'is_favorite' => false,
                'product_id' => $productId,
            ]);
        } else {
            $wishlist->products()->syncWithoutDetaching([$productId]);
            return $this->success([
                'success' => true,
                'is_favorite' => true,
                'product_id' => $productId,
            ]);
        }
    }

    private function formatWishlist(Wishlist $w): array
    {
        return [
            'id' => (string) $w->id,
            'user_id' => $w->user_id,
            'name' => $w->name,
            'description' => $w->description ?? '',
            'icon' => $w->icon ?? '✨',
            'is_default' => (bool) $w->is_default,
            'created_at' => $w->created_at?->toISOString() ?? now()->toISOString(),
            'product_ids' => $w->products->pluck('id')->map(fn($id) => (int) $id)->values()->toArray(),
        ];
    }
}
