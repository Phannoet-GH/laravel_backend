<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Order;
use App\Models\OrderItem;
use App\Security\Sanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $userId = $request->has('user_id')
            ? Sanitizer::cleanString($request->query('user_id'), 60)
            : null;

        $query = Order::with('items')->latest();
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        $orders = $query->get()->map(fn($o) => $this->formatOrder($o));

        return $this->success($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $items = $request->input('items');
        if (empty($items) || !is_array($items)) {
            return $this->error('Order must contain at least one item.', 422);
        }

        $orderId = 'ord_' . time() . '_' . bin2hex(random_bytes(3));
        $orderNumber = '#LX-' . rand(100000, 999999);
        $trackingNumber = 'TRK-' . rand(1000000, 9999999);

        $subtotal = Sanitizer::cleanFloat($request->input('subtotal', 0.0));
        $shippingFee = Sanitizer::cleanFloat($request->input('shipping_fee', 0.0));
        $discountAmount = Sanitizer::cleanFloat($request->input('discount_amount', 0.0));
        $totalAmount = Sanitizer::cleanFloat($request->input('total_amount', ($subtotal - $discountAmount + $shippingFee)));

        $userId = Sanitizer::cleanString($request->input('user_id', 'guest'), 60);
        $shippingAddress = Sanitizer::cleanString($request->input('shipping_address', '14 Market Street, New York, NY 10001'), 255);
        $paymentMethod = Sanitizer::cleanString($request->input('payment_method', 'Visa •••• 4589'), 100);
        $promoCode = $request->filled('promo_code') ? Sanitizer::cleanString($request->input('promo_code'), 50) : null;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'id' => $orderId,
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discountAmount,
                'promo_code' => $promoCode,
                'shipping_address' => $shippingAddress,
                'payment_method' => $paymentMethod,
                'status' => 'Processing',
                'tracking_number' => $trackingNumber,
            ]);

            foreach ($items as $item) {
                if (!is_array($item)) continue;

                $productId = (int) ($item['product_id'] ?? ($item['product']['id'] ?? 1));
                $productName = Sanitizer::cleanString($item['product_name'] ?? ($item['product']['name'] ?? 'Product'), 150);
                $productImage = $item['product_image'] ?? ($item['product']['image_url'] ?? ($item['product']['image'] ?? ''));
                $quantity = (int) ($item['quantity'] ?? ($item['qty'] ?? 1));
                $price = Sanitizer::cleanFloat($item['price'] ?? ($item['product']['price'] ?? 0.0));
                $selectedSize = Sanitizer::cleanString($item['selected_size'] ?? ($item['size'] ?? 'M'), 50);
                $selectedColor = Sanitizer::cleanString($item['selected_color'] ?? ($item['color'] ?? 'Default'), 50);

                OrderItem::create([
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'product_image' => $productImage,
                    'quantity' => max(1, $quantity),
                    'price' => $price,
                    'selected_size' => $selectedSize,
                    'selected_color' => $selectedColor,
                ]);
            }

            DB::commit();

            $order->load('items');
            return $this->success($this->formatOrder($order), [], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }

    public function track(Request $request): JsonResponse
    {
        $tracking = Sanitizer::cleanString($request->query('tracking_number') ?: $request->query('order_number', ''), 60);

        if (empty($tracking)) {
            return $this->error('Tracking number or order number is required.', 400);
        }

        $order = Order::with('items')
            ->where('tracking_number', $tracking)
            ->orWhere('order_number', $tracking)
            ->first();

        if (!$order) {
            return $this->error("Order with tracking '{$tracking}' not found.", 404);
        }

        return $this->success($this->formatOrder($order));
    }

    private function formatOrder(Order $o): array
    {
        return [
            'id' => (string) $o->id,
            'order_number' => $o->order_number,
            'user_id' => $o->user_id,
            'total_amount' => (float) $o->total_amount,
            'subtotal' => (float) $o->subtotal,
            'shipping_fee' => (float) $o->shipping_fee,
            'discount_amount' => (float) $o->discount_amount,
            'promo_code' => $o->promo_code,
            'shipping_address' => $o->shipping_address,
            'payment_method' => $o->payment_method,
            'status' => $o->status,
            'tracking_number' => $o->tracking_number,
            'created_at' => $o->created_at?->toISOString() ?? now()->toISOString(),
            'items' => $o->items->map(function (OrderItem $item) {
                return [
                    'id' => (int) $item->id,
                    'product_id' => (int) $item->product_id,
                    'product_name' => $item->product_name,
                    'product_image' => $item->product_image,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'selected_size' => $item->selected_size,
                    'selected_color' => $item->selected_color,
                ];
            })->toArray(),
        ];
    }
}
