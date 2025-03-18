# 專案架構

> 本專案主要使用 Laravel 建構完整服務，並透過容器化技術進行部署。

---

## 1. 整體架構

本專案採用 MVC 架構，使用 Laravel 框架開發，並透過 Docker 容器技術實現開發和部署環境的一致性。系統分為以下幾個主要部分：

1. **Laravel 應用程式** - 存放於 `laravel-app` 目錄，實作核心業務邏輯
2. **部署配置** - 存放於 `deployment` 目錄，包含所有 Docker 相關配置
3. **文件說明** - 存放於 `docs` 目錄，包含架構設計和使用說明

### 1.1 技術棧

- **後端框架**: Laravel 10
- **資料庫**: MariaDB
- **快取**: Redis
- **Web 伺服器**: Nginx
- **PHP 版本**: PHP 8.2
- **容器化技術**: Docker & Docker Compose
- **佇列處理**: Laravel Queue with Supervisor

---

## 2. 目錄結構

```sh
├── README.md            # 專案說明文件
├── laravel-app          # Laravel 應用程式目錄
├── deployment           # 部署相關配置
│   ├── docker           # Docker 配置檔
│   │   ├── db          # 資料庫持久化目錄
│   │   ├── nginx       # Nginx 配置
│   │   │   ├── conf.d
│   │   │   │   └── default.conf
│   │   │   └── sslkey # SSL 證書
│   │   ├── php        # PHP 環境配置
│   │   │   └── Dockerfile
│   │   ├── redis      # Redis 配置
│   │   └── supervisor # 監督程序配置
│   │       ├── conf.d
│   │       │   └── laravel-worker.conf
│   │       └── supervisord.conf
│   └── scripts         # 輔助腳本
│       ├── app
│       │   └── entrypoint.sh
│       └── database
│           └── init.sql
├── docker-compose.yml   # Docker 編排配置
└── docs                 # 文件目錄
    └── architecture.md  # 架構說明文件
```

## 3. 目錄詳細說明

### 3.1 Laravel 應用程式 (`laravel-app/`)

存放完整的 Laravel 專案程式碼，符合標準 Laravel 目錄結構：

```sh
├── app/                 # 應用核心代碼
│   ├── Console/        # 命令行指令
│   ├── Exceptions/     # 異常處理
│   ├── Http/           # HTTP 相關
│   │   ├── Controllers/ # 控制器
│   │   │   ├── Admin/    # 管理員控制器
│   │   │   ├── Main/     # 主要功能控制器
│   │   │   └── System/   # 系統功能控制器
│   │   ├── Middleware/  # 中間件
│   │   └── Kernel.php   # HTTP 核心
│   ├── Models/         # 資料模型
│   ├── Packages/       # 自定義套件
│   └── Providers/      # 服務提供者
├── bootstrap/          # 啟動相關文件
├── config/             # 配置文件
├── database/           # 資料庫相關
│   ├── migrations/     # 資料庫遷移
│   └── seeders/        # 資料填充
├── public/             # 公開資源目錄
├── resources/          # 資源文件
│   ├── js/             # JavaScript 文件
│   ├── css/            # CSS 文件
│   └── views/          # 視圖文件
├── routes/             # 路由定義
├── storage/            # 存儲目錄
└── tests/              # 測試目錄
```

### 3.2 部署配置 (`deployment/`)

#### 3.2.1 Docker 配置 (`docker/`)

- **db/**: 負責存放 MariaDB 資料庫的實際檔案外部持久化
- **nginx/**:
  - `conf.d/`: 存放 Nginx 設定檔（例如 default.conf）
  - `sslkey/`: 存放 SSL 憑證 (*.crt、*.key)，可獨立運作，無需依賴 app 容器
- **php/**:
  - 包含 `Dockerfile`，基於 Ubuntu 22.04，搭載 PHP 8.2，支援 GD 圖片處理
  - 工作目錄設定為 `/var/www/html`
  - 安裝 Composer 和 Supervisor
  - 網路命名為 dev
- **redis/**: 存放 Redis 相關設定
- **supervisor/**:
  - `supervisord.conf`: Supervisor 主設定檔
  - `conf.d/laravel-worker.conf`: Laravel 隊列工作進程設定

#### 3.2.2 輔助腳本 (`scripts/`)

- **app/**: 包含 `entrypoint.sh` 負責啟動 supervisor
- **database/**: 包含 `init.sql` 負責 SQL 初始化

### 3.3 Docker Compose 配置 (`docker-compose.yml`)

## 4. 技術配置詳情

### 4.1 PHP Dockerfile

```dockerfile
# 基於 Ubuntu 22.04
FROM ubuntu:22.04

# 安裝 PHP 8.2 及其一般泛用型擴展
RUN apt-get update && apt-get install -y \
    php8.2-fpm \
    php8.2-gd \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-mysql \
    php8.2-redis \
    composer \
    supervisor \
    geoip-bin

# 設置工作目錄
WORKDIR /var/www/html
```

### 4.2 SQL初始化

```sql
/* 建立 develop 使用者 */
CREATE USER IF NOT EXISTS 'develop'@'%' IDENTIFIED BY 'JdbQ8kVoB5';

/* 設定全域權限 */
GRANT ALTER, CREATE, INSERT, SELECT, DELETE, TRIGGER, REFERENCES, UPDATE, DROP, INDEX ON *.* TO 'develop'@'%';
REVOKE CREATE ROUTINE, CREATE VIEW, CREATE USER, SHOW VIEW, ALTER ROUTINE, EVENT, SUPER, RELOAD, FILE, SHOW DATABASES, SHUTDOWN, REPLICATION CLIENT, GRANT OPTION, PROCESS, REPLICATION SLAVE, EXECUTE, LOCK TABLES, CREATE TEMPORARY TABLES ON *.* FROM 'develop'@'%';

/* 更新所有使用者權限 */
FLUSH PRIVILEGES;
```

### 4.3 Docker Compose 配置

```yaml
version: '3'

services:
  # PHP 應用容器
  app:
    build: ./deployment/docker/php
    volumes:
      - ./laravel-app:/var/www/html
    depends_on:
      - db
      - redis

  # Nginx Web 伺服器
  web:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./deployment/docker/nginx/conf.d:/etc/nginx/conf.d
      - ./deployment/docker/nginx/sslkey:/etc/nginx/ssl
      - ./laravel-app:/var/www/html
    depends_on:
      - app

  # MariaDB 資料庫
  db:
    image: mariadb:10.6
    volumes:
      - ./deployment/docker/db:/var/lib/mysql
      - ./deployment/scripts/database/init.sql:/docker-entrypoint-initdb.d/init.sql
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: laravel

  # Redis 快取
  redis:
    image: redis:alpine
    volumes:
      - ./deployment/docker/redis:/data

networks:
  default:
    name: dev
```

## 5. 程式架構

### 5.1 MVC 架構

本專案遵循 Laravel 的 MVC (Model-View-Controller) 架構：

- **Model**: 位於 `app/Models/` 目錄，處理資料存取邏輯
- **View**: 位於 `resources/views/` 目錄，負責使用者介面
- **Controller**: 位於 `app/Http/Controllers/` 目錄，處理業務邏輯

### 5.2 模組化架構

控制器依功能區分為三個主要模組：

1. **Admin**: 管理員相關功能
2. **Main**: 主要業務邏輯
3. **System**: 系統功能

### 5.3 自定義套件

位於 `app/Packages/` 目錄，包含可重用的功能組件。

### 5.4 佇列處理

系統使用 Laravel Queue 處理異步任務，由 Supervisor 管理：

- `laravel-worker.conf` 定義佇列工作進程
- Supervisor 確保佇列處理持續可用

## 6. 開發與部署流程

### 6.1 本機開發

1. 複製專案
2. 執行初始化腳本: `sh ./deployment/scripts/manage/init-project.sh`
3. 設定 SSL 憑證: `sh ./deployment/scripts/manage/ssl-certificate.sh`
4. 啟動容器: `docker-compose up -d`

### 6.2 部署流程

1. 準備伺服器環境，安裝 Docker 和 Docker Compose
2. 複製專案到伺服器
3. 設定環境變數與配置
4. 執行部署腳本
5. 啟動容器服務