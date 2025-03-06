<?php
/**
 * User: paul
 * Date: 2025/3/6
 * Time: 16:29
 */

namespace App\Http\Controllers\System;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;

/**
 * Class HealthCheckController
 *
 * @package  App\Http\Controllers\System
 * @Author   : paul
 * @DateTime : 2025/3/6 18:08
 */
class HealthCheckController
{
    /**
     * @return JsonResponse
     * @Author   : paul
     * @DateTime : 2025/3/6 18:08
     */
    public function check(): JsonResponse
    {
        try {
            // 檢查版本
            $version = File::exists(base_path('version.txt')) ? trim(File::get(base_path('version.txt'))) : 'unknown';
        } catch (\Exception $e) {
            $version = 'Error: ' . $e->getMessage();
        }

        try {
            // 檢查 DB 連線
            DB::connection()->getPdo();
            $db_status = true;
        } catch (\Exception $e) {
            $db_status = 'Error: ' . $e->getMessage();
        }

        try {
            // 檢查 Redis 連線
            Redis::ping();
            $redisStatus = true;
        } catch (\Exception $e) {
            $redisStatus = 'Error: ' . $e->getMessage();
        }

        return response()->json(
            [
                'env'       => app()->environment(),
                'version'   => $version,
                'db_status' => $db_status,
                'redis'     => $redisStatus,
            ]
        );
    }
}
