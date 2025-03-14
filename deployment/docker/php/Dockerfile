FROM ubuntu:22.04

ENV LOCAL_CONFIG_DIR=deployment/docker
ENV CONTAINER_LARAVEL_DIR=/var/www/html
ENV DEBIAN_FRONTEND=noninteractive

# 更新系統並安裝基本工具和依賴
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        software-properties-common \
        ca-certificates gnupg curl \
        python3 python3-pip procps cron sysstat \
        vim iftop htop iotop lsof git gcc make screen g++ \
        imagemagick zip wget curl zsh ntpdate supervisor \
        libgeoip-dev libicu-dev && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get clean

# Add PHP PPA and install PHP 8.2 with extensions
RUN apt-get update && \
    add-apt-repository ppa:ondrej/php -y && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        php8.2 php8.2-fpm php8.2-curl php8.2-pgsql php8.2-mysqli php8.2-dev php8.2-odbc \
        php8.2-pspell php8.2-mysql php8.2-snmp php8.2-pdo php8.2-ldap \
        php8.2-gd php8.2-bcmath php8.2-enchant php8.2-mbstring php8.2-xml \
        php8.2-cli php8.2-common php8.2-soap php8.2-zip php8.2-igbinary \
        php8.2-memcache php8.2-memcached php8.2-redis php-pear php8.2-intl && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get clean

# 自動同步時區
RUN cp -rf /etc/localtime /etc/localtime.orig && \
    cp -rf /usr/share/zoneinfo/Asia/Taipei /etc/localtime && \
    echo "# 每 30 分鐘校正一次" >> /etc/crontab && \
    echo "*/30 * * * * root /usr/sbin/ntpdate time.google.com > /dev/null 2>&1" >> /etc/crontab

# 安裝 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 配置 PHP-FPM
RUN mkdir -p /var/run/php-fpm /var/log/php-fpm /run/php && \
    chown www-data:www-data /var/run/php-fpm /var/log/php-fpm /run/php
COPY --chown=root:root ${LOCAL_CONFIG_DIR}/php-fpm.d/www.conf /etc/php/8.2/fpm/pool.d/www.conf

# 設置工作目錄
WORKDIR /var/www/html

# Laravel 專案
COPY --chown=root:root laravel-app ${CONTAINER_LARAVEL_DIR}

# 建立 www-data 使用者並調整 Laravel 儲存目錄權限
RUN id -u www-data &>/dev/null || useradd -r -s /bin/false -u 33 www-data && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 設定 Supervisor 配置
COPY ${LOCAL_CONFIG_DIR}/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY ${LOCAL_CONFIG_DIR}/supervisor/conf.d/laravel-worker.conf /etc/supervisor/conf.d/

# 安装 zsh 和 oh-my-zsh
RUN wget https://github.com/robbyrussell/oh-my-zsh/raw/master/tools/install.sh -O - | zsh || true
COPY --chown=root:root ${LOCAL_CONFIG_DIR}/zsh/.zshrc /root/.zshrc

# 複製 entrypoint 腳本並設置執行權限
COPY deployment/scripts/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

# 健康檢查
HEALTHCHECK --interval=30s --timeout=3s \
    CMD php-fpm8.2 -t && ps aux | grep '[s]upervisord' || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]