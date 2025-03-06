#!/bin/bash

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

cd "$DIR/../../docker/nginx/sslkey"

for file in *.crt;
    do echo $file;
    sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain $file;
done

echo "SSL Certificate Install Complate!";