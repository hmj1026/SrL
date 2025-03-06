<?php

return [
    'domains' => [
        //前台
        'main'         => [
            // 網域名稱
            'domain'     => env('APP_DOMAIN'),
            // 使用哪一個 middleware 規則
            'middleware' => 'main',
            // 接續 \App\Http\Controllers 之後的 namespace 第一個階層名稱
            'namespace'  => '\Main',
            // 對應資料夾 /route/ 底下的檔案名稱，如 /route/main.php
            'file'       => 'main.php',
            'prefix'     => '/',
        ],
        //後台
        'admin'        => [
            // 網域名稱
            'domain'     => 'admin.' . env('APP_DOMAIN'),
            // 使用哪一個 middleware 規則
            'middleware' => 'admin',
            // 執行 php artisan route:list 所顯示的 name 第一個前綴字名稱
            'name'       => 'admin',
            // 接續 \App\App\Http\Controllers 之後的 namespace 第一個階層名稱
            'namespace'  => '\Admin',
            // 對應資料夾 /route/ 底下的檔案名稱，如 /route/main.php
            'file'       => 'admin.php',
            'prefix'     => '/',
        ],
        'api'          => [
            //*必填網域名稱
            'domain'     => 'api.' . env('APP_DOMAIN'),
            // 使用哪一個 middleware 規則
            'middleware' => 'api',
            // 接續 \App\Http\Controllers 之後的 namespace 第一個階層名稱
            'namespace'  => '\Api',
            // 對應資料夾 /route/ 底下的檔案名稱，如 /route/main.php
            'file'       => 'api.php',
            'prefix'     => '/',
        ],
    ],
];
