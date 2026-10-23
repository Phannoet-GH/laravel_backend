<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScrambleFuzzTest extends TestCase
{
    // CATEGORY 1: Malformed & Corrupted Payloads
    public function test_01_malformed_broken_json_payload(): void
    {
        $response = $this->call(
            'POST',
            '/api/auth/login',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            '{"email": "broken_payload'
        );

        $status = $response->status();
        $this->assertGreaterThanOrEqual(400, $status);
        $this->assertLessThan(500, $status);
        $this->assertFalse($response->json('success'));
    }

    public function test_02_non_json_plaintext_body(): void
    {
        $response = $this->call(
            'POST',
            '/api/coupons/validate',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'text/plain',
                'HTTP_ACCEPT' => 'application/json',
            ],
            'RAW_PLAINTEXT_DATA_NOT_JSON'
        );

        $status = $response->status();
        $this->assertGreaterThanOrEqual(400, $status);
        $this->assertLessThan(500, $status);
        $this->assertFalse($response->json('success'));
    }

    public function test_03_empty_post_body_on_auth_login(): void
    {
        $response = $this->call(
            'POST',
            '/api/auth/login',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            ''
        );

        $this->assertEquals(422, $response->status());
        $this->assertFalse($response->json('success'));
    }

    // CATEGORY 2: Data Type Juggling & Scrambling
    public function test_04_array_type_injection_for_email_field(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => ['injected', 'array'],
            'password' => 'secret123',
        ]);

        $this->assertEquals(422, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_05_boolean_type_injection_for_password_field(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'emma.wills@example.com',
            'password' => true,
        ]);

        $status = $response->status();
        $this->assertTrue($status === 401 || $status === 422);
        $this->assertFalse($response->json('success'));
    }

    public function test_06_object_map_injection_for_order_items(): void
    {
        $response = $this->postJson('/api/orders', [
            'user_id' => 'emma.wills@example.com',
            'items' => 'string_instead_of_array',
        ]);

        $this->assertEquals(422, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_07_integer_type_injection_for_coupon_code(): void
    {
        $response = $this->postJson('/api/coupons/validate', [
            'code' => 999999,
            'subtotal' => 100.0,
        ]);

        $this->assertEquals(404, $response->status());
        $this->assertFalse($response->json('success'));
    }

    // CATEGORY 3: Boundary & Overflow Scrambling
    public function test_08_massive_10000_char_buffer_overflow_string(): void
    {
        $response = $this->postJson('/api/auth/profile', [
            'email' => 'emma.wills@example.com',
            'name' => str_repeat('A', 10000),
            'phone' => '+1 (555) 000-0000',
        ]);

        $status = $response->status();
        $this->assertTrue($status === 200 || $status === 422);
        $this->assertIsArray($response->json());
    }

    public function test_09_negative_product_id_lookup(): void
    {
        $response = $this->getJson('/api/products?id=-9999');

        $this->assertEquals(404, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_10_astronomical_integer_overflow_in_id(): void
    {
        $response = $this->getJson('/api/products?id=9999999999999999999999999999');

        $this->assertEquals(404, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_11_extreme_negative_star_rating_injection(): void
    {
        $response = $this->postJson('/api/reviews', [
            'product_id' => 1,
            'user_name' => 'Tester',
            'rating' => -100.0,
            'comment' => 'Stress test rating clamping.',
        ]);

        $this->assertEquals(201, $response->status());
        $this->assertGreaterThanOrEqual(1.0, (float) $response->json('data.rating'));
    }

    public function test_12_extreme_positive_star_rating_injection(): void
    {
        $response = $this->postJson('/api/reviews', [
            'product_id' => 1,
            'user_name' => 'Tester',
            'rating' => 99999.0,
            'comment' => 'Stress test rating clamping.',
        ]);

        $this->assertEquals(201, $response->status());
        $this->assertLessThanOrEqual(5.0, (float) $response->json('data.rating'));
    }

    // CATEGORY 4: Injection & Sanitization Resilience
    public function test_13_sql_injection_scrambled_query_parameter(): void
    {
        $response = $this->getJson('/api/products?search=' . urlencode("' OR '1'='1' -- "));

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    public function test_14_stored_xss_script_tag_in_review_comment(): void
    {
        $response = $this->postJson('/api/reviews', [
            'product_id' => 1,
            'user_name' => 'XSS Tester',
            'rating' => 5.0,
            'comment' => '<script>alert("XSS_EXPLOIT")</script>Love this jacket!',
        ]);

        $this->assertEquals(201, $response->status());
        $comment = $response->json('data.comment');
        $this->assertStringNotContainsString('<script>', $comment);
    }

    public function test_15_svg_event_handler_xss_in_shipping_address(): void
    {
        $response = $this->postJson('/api/addresses', [
            'user_id' => 'emma.wills@example.com',
            'recipient_name' => 'Emma <svg onload=alert(1)> Wills',
            'street' => '100 Main St <img src=x onerror=alert(2)>',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
        ]);

        $this->assertEquals(201, $response->status());
        $recipient = $response->json('data.recipient_name');
        $this->assertStringNotContainsString('<svg', $recipient);
    }

    public function test_16_null_byte_character_injection_in_user_id(): void
    {
        $response = $this->postJson('/api/addresses', [
            'user_id' => "emma\x00wills@example.com",
            'recipient_name' => "Emma\x00Wills",
            'street' => '123 Market St',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip_code' => '94103',
        ]);

        $this->assertEquals(201, $response->status());
        $userId = $response->json('data.user_id');
        $this->assertStringNotContainsString("\x00", $userId);
    }

    public function test_17_unicode_emoji_storm_in_personal_name(): void
    {
        $response = $this->postJson('/api/auth/profile', [
            'email' => 'emma.wills@example.com',
            'name' => 'Emma 🌟👑💎🔥🚀',
            'phone' => '+1 (555) 234-5678',
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertIsArray($response->json('data'));
    }

    // CATEGORY 5: Schema Scrambling & Missing Fields
    public function test_18_order_submission_with_empty_items_array(): void
    {
        $response = $this->postJson('/api/orders', [
            'user_id' => 'emma.wills@example.com',
            'items' => [],
        ]);

        $this->assertEquals(422, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_19_registration_with_all_empty_mandatory_fields(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => '',
            'password' => '',
        ]);

        $this->assertEquals(422, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_20_non_existent_coupon_code_lookup(): void
    {
        $response = $this->postJson('/api/coupons/validate', [
            'code' => 'INVALID_SCRAMBLED_VOUCHER_XYZ',
            'subtotal' => 100.0,
        ]);

        $this->assertEquals(404, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_21_tracking_non_existent_order_number(): void
    {
        $response = $this->getJson('/api/orders/track?tracking_number=TRK-DOES-NOT-EXIST-000');

        $this->assertEquals(404, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_22_add_wishlist_item_with_non_existent_product(): void
    {
        $response = $this->postJson('/api/wishlists/items', [
            'wishlist_id' => 'wl_default',
            'product_id' => 0,
        ]);

        $this->assertEquals(400, $response->status());
        $this->assertFalse($response->json('success'));
    }

    // CATEGORY 6: Routing & Path Traversal Scrambling
    public function test_23_completely_scrambled_route_uri(): void
    {
        $response = $this->getJson('/api/x_scrambled_unknown_endpoint_777');

        $this->assertEquals(404, $response->status());
        $this->assertFalse($response->json('success'));
    }

    public function test_24_path_traversal_injection_in_route(): void
    {
        $response = $this->getJson('/api/../../etc/passwd');

        $this->assertEquals(404, $response->status());
    }
}
