<?php

namespace App\Providers;

use App\Http\Controllers\System\HealthCheckController;
use Filament\Facades\Filament;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class RouteServiceProvider
 *
 * @package  App\Providers
 * @Author   : paul
 * @DateTime : 2025/3/6 18:10
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 拆分路由檔
        collect(config('route.domains'))->each(function ($config, $name) {
            // 設定 Route Map
            Route::domain(Arr::get($config, 'domain'))
                ->name(Arr::get($config, 'name', $name) . '.')
                ->middleware(Arr::get($config, 'middleware', $name))
                ->namespace($this->getNamespace(Arr::get($config, 'namespace', "\\" . Str::ucfirst(Str::camel($name)))))
                ->prefix(Arr::get($config, 'prefix', '/'))
                ->group($this->getGroupFile(Arr::get($config, 'file')));
        });

        // 系統健康檢查
        Route::get('/health-check', [HealthCheckController::class, 'check']);

        // 調整livewire使用設定好的middleware group而不是走預設的web
        app(\Livewire\Mechanisms\HandleRequests\HandleRequests::class)->setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)->middleware(Filament::getAuthGuard());
        });
    }

    /**
     * @param string $namespace
     *
     * @return string
     * @Author   : paul
     * @DateTime : 2025/3/6 18:10
     */
    private function getNamespace(string $namespace): string
    {
        return str_replace('\\\\', '\\', 'App\Http\Controllers' . '\\' . $namespace);
    }

    /**
     * @param string $file
     *
     * @return string
     * @Author   : paul
     * @DateTime : 2025/3/6 18:10
     */
    private function getGroupFile(string $file): string
    {
        return base_path('routes/' . $file);
    }
}
