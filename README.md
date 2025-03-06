# SrL

> 主要透過docker容器設定建立開發用框架內含有nginx以及redis還有db相關服務應用

## 操作流程

- 創建相關專案設定

```sh
sh ./deployment/scripts/manage/init-project.sh
```
    1. 輸入專案名稱
    2. 輸入專案網域
將會產生相關網域的簽證以及nginx設定檔

- 加入專案SSL簽證

```sh
sh ./deployment/scripts/manage/ssl-certificate.sh
```

- 將網域加入hosts檔案後，執行

```sh
docker-compose up -d
```

- 於瀏覽器輸入設定的網址檢查有無錯誤
