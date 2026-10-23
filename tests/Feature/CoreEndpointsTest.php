<?php

namespace Tests\Feature;

use Tests\TestCase;

class CoreEndpointsTest extends TestCase
{
    public function test_01_health_check(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'data' => [
                    'status' => 'online',
                ],
            ]);
    }

    public function test_02_csrf_token(): void
    {
        $response = $this->getJson('/api/csrf-token');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
            ])
            ->assertJsonStructure([
                'data' => ['csrf_token', 'expires_in'],
            ]);
    }

    public function test_03_get_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
            ]);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_04_get_products_all(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
            ])
            ->assertJsonStructure([
                'data',
                'meta' => ['count', 'category', 'search'],
            ]);

        $this->assertGreaterThanOrEqual(10, count($response->json('data')));
    }

    public function test_05_filter_products_apparel(): void
    {
        $response = $this->getJson('/api/products?category=Apparel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'meta' => [
                    'category' => 'Apparel',
                ],
            ]);

        $items = $response->json('data');
        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertEquals('Apparel', $item['category']);
        }
    }

    public function test_06_search_products_hoodie(): void
    {
        $response = $this->getJson('/api/products?search=hoodie');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
            ]);

        $items = $response->json('data');
        $this->assertNotEmpty($items);
    }

    public function test_07_get_product_detail(): void
    {
        $response = $this->getJson('/api/products?id=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'data' => [
                    'id' => 1,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'brand',
                    'price',
                    'image',
                    'image_url',
                    'rating',
                    'reviews',
                    'reviews_count',
                    'customer_reviews',
                ],
            ]);
    }

    public function test_08_validate_coupon_luxe20(): void
    {
        $response = $this->postJson('/api/coupons/validate', [
            'code' => 'LUXE20',
            'subtotal' => 100.0,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'data' => [
                    'code' => 'LUXE20',
                    'discount_type' => 'percent',
                    'discount_amount' => 20.0,
                ],
            ]);
    }

    public function test_09_validate_coupon_save10(): void
    {
        $response = $this->postJson('/api/coupons/validate', [
            'code' => 'SAVE10',
            'subtotal' => 60.0,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'data' => [
                    'code' => 'SAVE10',
                    'discount_type' => 'fixed',
                    'discount_amount' => 10.0,
                ],
            ]);
    }

    public function test_10_get_reviews_for_product(): void
    {
        $response = $this->getJson('/api/reviews?product_id=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
            ]);

        $this->assertIsArray($response->json('data'));
    }

    public function test_11_get_addresses_for_user(): void
    {
        $response = $this->getJson('/api/addresses?user_id=emma.wills@example.com');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
            ]);

        $this->assertIsArray($response->json('data'));
    }

    public function test_12_add_new_address(): void
    {
        $response = $this->postJson('/api/addresses', [
            'user_id' => 'emma.wills@example.com',
            'recipient_name' => 'Emma Wills',
            'street' => '14 Market Street',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'is_default' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status' => 201,
                'data' => [
                    'recipient_name' => 'Emma Wills',
                    'street' => '14 Market Street',
                ],
            ]);
    }

    public function test_13_get_wishlists_for_user(): void
    {
        $response = $this->getJson('/api/wishlists?user_id=emma.wills@example.com');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
            ]);

        $this->assertIsArray($response->json('data'));
    }

    public function test_14_toggle_favorite_item(): void
    {
        $response = $this->postJson('/api/favorites/toggle', [
            'user_id' => 'emma.wills@example.com',
            'product_id' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'data' => [
                    'product_id' => 1,
                ],
            ]);
    }

    public function test_15_update_user_profile(): void
    {
        $response = $this->postJson('/api/auth/profile', [
            'email' => 'emma.wills@example.com',
            'name' => 'Emma Wills',
            'phone' => '+1 (555) 234-5678',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
                'data' => [
                    'email' => 'emma.wills@example.com',
                    'name' => 'Emma Wills',
                ],
            ]);
    }

    public function test_16_get_user_orders(): void
    {
        $response = $this->getJson('/api/orders?user_id=emma.wills@example.com');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 200,
            ]);

        $this->assertIsArray($response->json('data'));
    }
}
