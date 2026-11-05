<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    /**
     * Memeriksa kesehatan sistem (Database & Cache).
     * Mengembalikan HTTP 200 jika healthy, HTTP 503 jika unhealthy.
     */
    public function check(Request $request)
    {
        $status = 'healthy';
        $httpCode = 200;
        $checks = [];

        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Koneksi basis data MySQL berhasil diverifikasi.',
            ];
        } catch (\Throwable $e) {
            $status = 'unhealthy';
            $httpCode = 503;
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Gagal menghubungi basis data: ' . $e->getMessage(),
            ];

            Log::error('Healthcheck probe failed: database is unreachable', [
                'actor' => 'healthcheck_probe',
                'client_ip' => $request->ip(),
                'action' => 'database_connectivity_check',
                'component' => 'database',
                'reason' => $e->getMessage(),
            ]);
        }

        try {
            $probeKey = 'health_probe_' . bin2hex(random_bytes(4));
            $probeValue = 'probe_ok_' . time();

            Cache::put($probeKey, $probeValue, 10);
            $retrieved = Cache::get($probeKey);
            Cache::forget($probeKey);

            if ($retrieved === $probeValue) {
                $checks['cache'] = [
                    'status' => 'healthy',
                    'message' => 'Operasi tulis, baca, dan hapus cache store berhasil diverifikasi.',
                ];
            } else {
                throw new \Exception('Integritas nilai cache tidak valid (mismatch).');
            }
        } catch (\Throwable $e) {
            $status = 'unhealthy';
            $httpCode = 503;
            $checks['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Operasi cache bermasalah: ' . $e->getMessage(),
            ];

            Log::error('Healthcheck probe failed: cache operation failed', [
                'actor' => 'healthcheck_probe',
                'client_ip' => $request->ip(),
                'action' => 'cache_connectivity_check',
                'component' => 'cache',
                'reason' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $httpCode);
    }
}