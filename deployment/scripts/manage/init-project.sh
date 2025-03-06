#!/bin/bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do
    TARGET="$(readlink "$SOURCE")"
    if [[ $SOURCE == /* ]]; then
        echo "SOURCE '$SOURCE' is an absolute symlink to '$TARGET'"
        SOURCE="$TARGET"
    else
        DIR="$( dirname "$SOURCE" )"
        echo "SOURCE '$SOURCE' is a relative symlink to '$TARGET' (relative to '$DIR')"
        SOURCE="$DIR/$TARGET"
    fi
done

RDIR="$( dirname "$SOURCE" )"
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

while true; do
    ConfirmMessage="請輸入自訂專案名稱?:"

    read -e -p "$ConfirmMessage" ProjectName

    if [[ -z "$ProjectName" ]]; then
        echo "$(tput setaf 1)錯誤! 專案名稱不得為空值$(tput sgr0)"
        continue
    fi

    break
done

while true; do
    read -e -p "請輸入專案主網域? (Ex: dev.domain.com.tw、dev.domain.com):" Domain

    if [[ -z "$Domain" ]]; then
        echo "$(tput setaf 1)錯誤! 專案網域不得為空值$(tput sgr0)"
        continue
    fi

    break
done

printf "專案名稱: $ProjectName\n專案主網域: $Domain\n"

read -p "請確認輸入的內容是否正確？(Y/n) \n" Reply
Reply=${Reply:-Y}
if [[ ! $Reply =~ ^[Yy]$ ]]; then
    exit
fi

# 產生nginx 設定檔
cat << EOF > $DIR/../../docker/nginx/conf.d/$ProjectName.conf
server {
    listen 80;
    server_name .$Domain;
    return 302 https://\$host\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name .$Domain;

    ssl_certificate /etc/nginx/sslkey/$ProjectName.crt;
    ssl_certificate_key /etc/nginx/sslkey/$ProjectName.key;

    access_log /var/log/nginx/$ProjectName.access.log;
    error_log /var/log/nginx/$ProjectName.error.log;

    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include /etc/nginx/conf.d/base/fastcgi_location.conf;
        include fastcgi_params;
    }
}
EOF

echo "$ProjectName.conf 產生成功!"

# 產生SSL憑證設定檔
cat << EOF > $DIR/tmp-ssl.conf
[req]
prompt = no
default_md = sha512
default_bits = 4096
distinguished_name = dn
x509_extensions = v3_req

[dn]
C = TW
ST = Taiwan
L = Taipei
O = YourCompany
OU = Security Department
emailAddress = security@example.com
CN = $Domain

[v3_req]
subjectAltName = @alt_names
keyUsage = critical, digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth

[alt_names]
DNS.1 = $Domain
DNS.2 = *.$Domain
DNS.3 = *.admin.$Domain
DNS.4 = *.api.$Domain
EOF

openssl req -x509 -new -nodes -sha512 -utf8 -days 36500 -newkey rsa:4096 \
    -keyout $DIR/../../docker/nginx/sslkey/$ProjectName.key \
    -out $DIR/../../docker/nginx/sslkey/$ProjectName.crt \
    -config $DIR/tmp-ssl.conf

#清除暫存設定檔
rm -f $DIR/tmp-ssl.conf

echo "SSL憑證 $ProjectName.key $ProjectName.crt 產生成功!"

# 產生host設定
cat << EOF
#請複製到host檔案並調整

#MAC(/etc/hosts):
127.0.0.1 www.$Domain
127.0.0.1 admin.$Domain
127.0.0.1 api.$Domain
EOF