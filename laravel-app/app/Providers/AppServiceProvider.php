<?php

namespace App\Providers;

use App\Packages\Supports\PosixUser;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Schema::defaultStringLength(200);

        // 解決分頁器 request 問題
        Paginator::currentPathResolver(function () {
            return $this->app['url']->to('/') . '/' . $this->app['request']->path();
        });

        // 動態設定 Logs 檔路徑
        $log_path = storage_path(
            'logs/' . php_sapi_name() . '/' . PosixUser::getCurrentUserName() . '/laravel.log'
        );
        Config::set('logging.channels.single.path', $log_path);
        Config::set('logging.channels.daily.path', $log_path);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (parse_url(config('app.url'), PHP_URL_SCHEME) === 'https') {
            URL::forceScheme('https');
        }

        if ($this->app->environment('dev')) {
            DB::listen(static function ($query) {
                // 使用 Log 來記錄 SQL 查詢，包括其綁定和執行時間
                Log::info(
                    $query->sql,
                    [
                        'bindings' => $query->bindings,
                        'time'     => $query->time,
                    ]
                );
            });
        }
    }
}
