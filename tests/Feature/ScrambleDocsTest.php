<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScrambleDocsTest extends TestCase
{
    public function test_scramble_documentation_ui_accessible(): void
    {
        $response = $this->get('/docs/api');

        $response->assertStatus(200);
        $response->assertSee('LuxeCart API Documentation & Testing Console');
    }

    public function test_scramble_openapi_spec_generation(): void
    {
        $response = $this->get('/docs/api.json');

        $response->assertStatus(200);
        $spec = $response->json();

        $this->assertArrayHasKey('openapi', $spec);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('/products', $spec['paths']);
        $this->assertArrayHasKey('/categories', $spec['paths']);
        $this->assertArrayHasKey('/orders', $spec['paths']);
    }
}
