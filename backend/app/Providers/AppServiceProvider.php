<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\PDO as PrometheusPdoStorage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CollectorRegistry::class, function ($app) {
            if ($app->environment('testing')) {
                return new CollectorRegistry(new InMemory());
            }

            $dbPath = storage_path('framework/cache/prometheus.sqlite');
            $directory = dirname($dbPath);

            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $pdo = new \PDO('sqlite:' . $dbPath, null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ]);
            $pdo->exec('PRAGMA journal_mode = WAL;');
            $pdo->exec('PRAGMA busy_timeout = 5000;');

            return new CollectorRegistry(new PrometheusPdoStorage($pdo));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
