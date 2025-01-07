[中文](./README.md) | English
# Admin.IM

<div align="center">
    <img alt="Admin.IM Screenshot" src="./public/static/assets/images/logo.svg" width="120">
</div>

<div align="center">

[![PHP](https://img.shields.io/badge/PHP-8.x-blue.svg)](https://php.net)
[![MineAdmin](https://img.shields.io/badge/MineAdmin-v2.0&nbsp;LTS-green.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Apache&nbsp;2.0-yellow.svg)](https://php.net)
</div>

## 📖 Projects Description

Admin.IM is an open source network detection and server management system. The backend and interface are developed based on MineAdmin, using the PHP Hyperf framework driven by Swoole, full coroutine scheduling + asynchronous I/O implementation, the system performance is excellent, and it can easily handle a large number of concurrent requests.

The front end is developed using Vue3 + Vite5 + TypeScript + Pinia + Arco Design, and is adaptive to multiple terminals. The client Agent is developed using Golang 1.22 and supports multiple platforms such as Linux, Windows, and MacOS.

## ✨ System Features

- 🔥 **High-performance architecture**: Hyperf framework based on Swoole, full-coroutine asynchronous implementation
- 🎨 **Modern interface**: Vue3 + Arco Design, adaptive multi-terminal display
- 🧩 **Plug-in design**: ICMP Ping, TCPing, more plug-ins are under development
- 🌐 **Multi-language support**: Built-in multi-language function, support English, Simplified Chinese, Traditional Chinese switching
- ⏺️ **Log audit**: User login, system operation records can be queried at any time
- 🛡️ **Stable and reliable**: After rigorous testing, suitable for production environment deployment
- 🖥️ **Cross-platform support**: Agent supports Linux, Windows, MacOS

## 📦 Warehouse address
- Server Admin.IM: [Github](https://github.com/AdmUU/Admin.IM) | [Gitee](https://gitee.com/AdmUU/Admin.IM)
- Client Adm-Agent: [Github](https://github.com/AdmUU/adm-agent) | [Gitee](https://gitee.com/admuu/adm-agent)
- Front-end UI Adm-Frontend-User: [Github](https://github.com/AdmUU/adm-frontend-user) | [Gitee](https://gitee.com/admuu/adm-frontend-user)
- Backend UI Adm-Frontend-Admin: [Github](https://github.com/AdmUU/adm-frontend-admin) | [Gitee](https://gitee.com/admuu/adm-frontend-admin)

## 🚀 Source installation

### Environmental requirements
- Swoole >= 5.0, turn off `Short Name`
- PHP >= 8.1 并开启以下扩展：
  - curl
  - fileinfo
  - mbstring
  - json
  - pdo
  - openssl
  - redis
  - pcntl
- MySQL >= 5.7
- Redis >= 6.2.0
- Composer >= 2.x
- Git >= 2.x

### Download project

```bash
# Clone project
git clone https://github.com/AdmUU/Admin.IM.git

# Install dependencies
cd Admin.IM
composer install

# Copy configuration file
cp .env.example .env
```

### Configure installation

Configure `.env` , fill in the MySQL database, Redis connection and other information, and execute the installation command:

```shell
#Install database and plug-in
php bin/hyperf.php mine:install
```
Start the service

```bash
php bin/hyperf.php start
```

## 🐳 Docker deployment

It is recommended to use Docker Compose to deploy online.

### Prerequisites
- System memory is 1G or more. If it is installed on the same server as the database, it should be at least 2G.
- Install Docker and Docker Compose plugins.
- Install MySQL and Redis. (You can also use built-in MySQL and Redis)

### Quick deployment

1. Create a deployment directory and enter:
```bash
mkdir admin-im && cd admin-im
```

2. Create and edit the environment configuration file .env:
```bash
vim .env
```

```properties
#.env
ADM_DB_HOST=mysql                    #MySQL address
ADM_DB_PORT=3306                     #MySQL port
ADM_DB_USERNAME=user                 #MySQL user name
ADM_DB_PASSWORD=password             #MySQL password
ADM_DB_DATABASE=db_name              #MySQL database name
ADM_REDIS_HOST=redis                 #Redis address
ADM_REDIS_PORT=6379                  #Redis port
ADM_REDIS_PASSWORD=redis_password    #Redis password
ADM_PORT_HTTP=8090                   #Access port number

#ADM_DB_ROOT_PASSWORD=admmysqlrootpwd # Built-in MySQL root password
```

>If MySQL and Redis are not installed in advance, uncomment the ADM_DB_ROOT_PASSWORD configuration item above, set the MySQL root password, and Docker will automatically install it during deployment. The data in the built-in database has been persisted and can be used directly.

3. Create and edit the container orchestration file docker-compose.yml:
```bash
vim docker-compose.yml
```

```yaml
#docker-compose.yml
x-common-vars: &common-vars
  APP_VERSION: ${APP_VERSION:-latest}
  TZ: ${TIMEZONE:-Asia/Shanghai}

services:
  admin-im:
    image: ${DOCKER_REGISTRY:-docker.io}/admuu/admin.im:1
    container_name: admin-im
    working_dir: /opt/www
    volumes:
      - ./data:/data
      - ./data/upload:/opt/www/public/upload
      - ./data/logs:/opt/www/runtime/logs/debug
      - /etc/timezone:/etc/timezone:ro
      - /etc/localtime:/etc/localtime:ro
    env_file:
      - .env
    networks:
      - server
    restart: unless-stopped
    depends_on:
      mysql:
        condition: service_healthy
        required: false
      redis:
        condition: service_healthy
        required: false
    healthcheck:
      test: ["CMD", "wget", "--no-verbose", "--tries=1", "--spider", "http://127.0.0.1:9501/adm/health"]
      interval: 5s
      timeout: 5s
      retries: 5
      start_period: 30s
  adm-frontend:
    image: ${DOCKER_REGISTRY:-docker.io}/admuu/adm-frontend:1
    container_name: adm-frontend
    volumes:
      - /etc/timezone:/etc/timezone:ro
      - /etc/localtime:/etc/localtime:ro
    ports:
      - "${ADM_PORT_HTTP:-8090}:8090"
    networks:
      - server
    depends_on:
      - admin-im
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "wget", "--no-verbose", "--tries=1", "--spider", "http://127.0.0.1:8090/"]
      interval: 5s
      timeout: 5s
      retries: 5
      start_period: 30s
  mysql:
    image: bitnami/mysql:8.4
    container_name: adm-mysql
    volumes:
      - mysql:/bitnami/mysql/data
      - /etc/timezone:/etc/timezone:ro
      - /etc/localtime:/etc/localtime:ro
    environment:
      <<: *common-vars
      MYSQL_ROOT_PASSWORD: ${ADM_DB_ROOT_PASSWORD:-admmysqlrootpwd}
      MYSQL_USER: ${ADM_DB_USERNAME}
      MYSQL_PASSWORD: ${ADM_DB_PASSWORD}
      MYSQL_DATABASE: ${ADM_DB_DATABASE}
    networks:
      - server
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${ADM_DB_ROOT_PASSWORD:-admmysqlrootpwd}"]
      interval: 5s
      timeout: 5s
      retries: 5
      start_period: 30s
    restart: unless-stopped
    profiles:
      - mysql
  redis:
    image: bitnami/redis
    container_name: adm-redis
    volumes:
      - redis:/bitnami/redis/data
      - /etc/timezone:/etc/timezone:ro
      - /etc/localtime:/etc/localtime:ro
    environment:
      <<: *common-vars
      REDIS_PASSWORD: ${ADM_REDIS_PASSWORD}
      REDIS_AOF_ENABLED: yes
      REDIS_RDB_POLICY_DISABLED: no
      REDIS_RDB_POLICY: 900#1 600#5 300#10 120#50 60#1000 30#10000
    command: /opt/bitnami/scripts/redis/run.sh --maxmemory ${ADM_REDIS_MAX_MEMORY:-512mb} --maxmemory-policy allkeys-lru
    sysctls:
      net.core.somaxconn: 1024
    networks:
      - server
    healthcheck:
      test: ["CMD", "redis-cli", "-a", "${ADM_REDIS_PASSWORD}", "ping"]
      interval: 5s
      timeout: 3s
      retries: 3
      start_period: 10s
    restart: unless-stopped
    profiles:
      - redis
name: admuu
networks:
  server:
    driver: bridge
volumes:
  mysql:
    driver: local
  redis:
    driver: local
```
4. Start the service:

>Method 1
```bash
# Use an external database and start directly
docker compose up -d
```

>Method 2
```bash
# Use the built-in database to automatically install Mysql and Redis
docker compose --profile mysql --profile redis up -d
```

5. Check the installation progress:

The system installation takes 1 to 2 minutes. After the installation is successful, the default username is admin, and the default password needs to be checked from the installation log.
```bash
#Monitor the installation progress
docker logs -f -n 20 admin-im

#View the default password
docker logs admin-im | grep "Default password"
```

6. Access the system:
```bash
#Frontend
http://localhost:8090

#Backend
http://localhost:8090/manage/
```

## 📚 Documentation

For detailed system description, please visit [Project Documentation](https://doc.admin.im)

## 📝 Open Source Agreement

This project adopts the Apache License Version 2.0 agreement. For details, please refer to the [LICENSE](LICENSE) file.

## 🙏 Acknowledgements

[MineAdmin](https://www.mineadmin.com)

ljk123/captcha

[Hyperf](https://hyperf.io)

[Swoole](https://www.swoole.com)

[Vue](https://vuejs.org/)

[Arco](https://arco.design/vue)

[Vite](https://vite.dev)

## 📸 Screenshots

<img alt="Admin.IM Screenshot" src="https://get.admin.im/screenshot/01.png" width="100%" />
<img alt="Admin.IM Screenshot" src="https://get.admin.im/screenshot/02.png" width="100%" />
<img alt="Admin.IM Screenshot" src="https://get.admin.im/screenshot/03.png" width="100%" />