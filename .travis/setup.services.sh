#!/usr/bin/env bash
sudo docker pull mcr.microsoft.com/mssql/server:2022-latest &
docker run -e "ACCEPT_EULA=Y" -e "MSSQL_SA_PASSWORD=mineadmin123,./" \
   -p 1433:1433 --name mssql --hostname mssql \
   -d \
   mcr.microsoft.com/mssql/server:2022-latest &
docker run --name mysql -p 3306:3306 -e MYSQL_ALLOW_EMPTY_PASSWORD=true -d mysql:${MYSQL_VERSION} --bind-address=0.0.0.0 --default-authentication-plugin=mysql_native_password &
docker run --name postgres -p 5432:5432 -e POSTGRES_PASSWORD=postgres -d postgres:${PGSQL_VERSION} &
docker run --name redis -p 6379:6379 -d redis &
docker run -d --restart=always --name rabbitmq  -e RABBITMQ_DEFAULT_USER=mineadmin -e RABBITMQ_DEFAULT_PASS=123456 -p 4369:4369 -p 5672:5672 -p 15672:15672 -p 25672:25672 rabbitmq:management-alpine &
wait
sleep 30
docker ps -a