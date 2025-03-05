#!/bin/bash

echo "Starting startup script..."

SUPERVISOR_CONF_DIR="/etc/supervisor/conf.d"
PROJECT_DIR="/var/www/html"
CRONTAB_FILE="/etc/crontab"

# 設置正確權限
find ${PROJECT_DIR}/storage -type d -exec chmod 2755 {} \;
chown www-data:www-data -R ${PROJECT_DIR}/storage
find ${PROJECT_DIR}/bootstrap/cache -type d -exec chmod 2755 {} \;
chown www-data:www-data -R ${PROJECT_DIR}/bootstrap/cache

# 確保必要目錄存在
mkdir -p ${SUPERVISOR_CONF_DIR} /var/log/supervisor /var/run/supervisor /var/log/php-fpm /var/run/php-fpm /run/php
chown www-data:www-data /var/run/php-fpm /var/log/php-fpm /run/php

# 刪除已存在的 supervisor socket 文件
if [ -e /var/run/supervisor/supervisor.sock ]; then
    echo "Removing stale supervisor socket..."
    rm /var/run/supervisor/supervisor.sock
fi

# 檢查並啟動 cron 服務
service cron start
if [ $? -ne 0 ]; then
    echo "Failed to start cron service"
    exit 1
fi

# 啟動 PHP-FPM
php-fpm8.2 -D
if [ $? -ne 0 ]; then
    echo "Failed to start php-fpm8.2"
    exit 1
fi

# 啟動 Supervisor
echo "Starting supervisord..."
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf
if [ $? -ne 0 ]; then
    echo "Failed to start supervisord"
    exit 1
fi

# 檢查服務運行狀態
sleep 3
if pgrep supervisord > /dev/null; then
    echo "supervisord is running"
else
    echo "supervisord failed to start"
    exit 1
fi

if pgrep php-fpm > /dev/null; then
    echo "php-fpm is running"
else
    echo "php-fpm failed to start"
    exit 1
fi

# 保持容器運行
tail -f /dev/null