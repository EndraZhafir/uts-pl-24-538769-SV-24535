<?php

namespace Tests\Feature;

use Tests\TestCase;

class MetricsTest extends TestCase
{
    public function test_metrics_endpoint_exposes_http_requests_total(): void
    {
        $this->get('/')->assertStatus(200);

        $response = $this->get('/metrics');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('http_requests_total', $response->getContent());
    }
}
