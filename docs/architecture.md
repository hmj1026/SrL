# 專案架構
===

> 本專案主要使用 Laravel 建構完整服務，並透過容器化技術進行部署。

---

## 資料結構

- 專案的資料夾結構如下：

```sh
├── README.md
├── laravel-app
├── deployment
│   ├── docker
│   │   ├── db
│   │   ├── nginx
│   │   │   ├── conf.d
│   │   │   │   └── default.conf
│   │   │   └── sslkey
│   │   ├── php
│   │   │   └── Dockerfile
│   │   ├── redis
│   │   └── supervisor
│   │       ├── conf.d
│   │       │   └── laravel-worker.conf
│   │       └── supervisord.conf
│   └── scripts
│       ├── app
│       │   └── entrypoint.sh
│       └── database
│           └── init.sql
├── docker-compose.yml
└── docs
    └── architecture.md
```

## 資料夾說明
    1. laravel-app資料夾
        - 存放完整的 Laravel 專案程式碼
    2. deployment資料夾
        - docker/ : 裡面包含所有需要的服務相關設定檔文件
        - db/ : 負責存放 db 的實際檔案外部持久化，使用mariadb
        - nginx/ 
            - conf.d：存放 Nginx 設定檔（例如 default.conf）。
            - sslkey：存放 SSL 憑證 (*.crt、*.key)，可獨立運作，無需依賴 app 容器
        - supervisor/ 放置相關設定檔外部文件，給app容器 entrypoint.sh 載入以及於Dockerfile使用
        - php/ 
            - 包含 Dockerfile，基於 Ubuntu 22.04，搭載 PHP 8.2，支援 GD 圖片處理
            - 工作目錄設定為 /var/www/html
            - 安裝 Composer 和 Supervisor
            - 網路命名為 dev
        - redis/：存放 Redis 相關設定（如果有）
        - supervisor/：存放 Supervisor 的設定檔。
            - supervisord.conf：Supervisor 主設定檔。
            - laravel-worker.conf：Laravel 隊列工作進程設定。    
        - scripts/
            - app/ 有 entrypoint.sh 負責啟動supervisor
            - database/ 有一個 sql sript負責sql初始化
    3. docker-compose.yaml 

### PHP Dockerfile
    - 基於 ubuntu:22.04
    - 安裝 PHP 8.2 及其一般泛用型擴展（例如 php8.2-fpm、GD 等）
    - 安裝 Composer 和 Supervisor, Geoip
    - 工作目錄設定為 /var/www/html

### SQL初始化

```sql
/* 建立 develop 使用者 */
CREATE USER IF NOT EXISTS 'develop'@'%' IDENTIFIED BY 'JdbQ8kVoB5';

/* 設定全域權限 */
GRANT ALTER, CREATE, INSERT, SELECT, DELETE, TRIGGER, REFERENCES, UPDATE, DROP, INDEX ON *.* TO 'develop'@'%';
REVOKE CREATE ROUTINE, CREATE VIEW, CREATE USER, SHOW VIEW, ALTER ROUTINE, EVENT, SUPER, RELOAD, FILE, SHOW DATABASES, SHUTDOWN, REPLICATION CLIENT, GRANT OPTION, PROCESS, REPLICATION SLAVE, EXECUTE, LOCK TABLES, CREATE TEMPORARY TABLES ON *.* FROM 'develop'@'%';

/* 更新所有使用者權限 */
FLUSH PRIVILEGES;
```
### docker-compose.yml
- 定義以下服務：
    - app：PHP 應用容器，依賴 db 和 redis
    - web：web nginx 容器，依賴 app
    - db：MariaDB 資料庫
    - redis：Redis 快取
    - 網路命名為 dev