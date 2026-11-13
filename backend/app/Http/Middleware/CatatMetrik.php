<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpFoundation\Response;

class CatatMetrik
{
    public function __construct(private readonly CollectorRegistry $registry)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('metrics')) {
            return $next($request);
        }

        $start = microtime(true);
        $response = $next($request);
        $duration = microtime(true) - $start;

        $routePattern = $request->route()?->uri() ?? 'unmatched';
        $method = $request->method();
        $status = (string) $response->getStatusCode();

        $counter = $this->registry->getOrRegisterCounter(
            '',
            'http_requests_total',
            'Total number of HTTP requests processed by MyBook backend',
            ['method', 'route', 'status']
        );
        $counter->inc([$method, $routePattern, $status]);

        $histogram = $this->registry->getOrRegisterHistogram(
            '',
            'http_request_duration_seconds',
            'HTTP request processing duration in seconds',
            ['method', 'route'],
            [0.01, 0.05, 0.1, 0.25, 0.5, 1.0, 2.5]
        );
        $histogram->observe($duration, [$method, $routePattern]);

        return $response;
    }
}
