中文 | [English](./README-en.md)
# Admin.IM

<div align="center">
    <img alt="Admin.IM Screenshot" src="./public/static/assets/images/logo.svg" width="120">
</div>

<div align="center">

[![PHP](https://img.shields.io/badge/PHP-8.x-blue.svg)](https://php.net)
[![MineAdmin](https://img.shields.io/badge/MineAdmin-v2.0&nbsp;LTS-green.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Apache&nbsp;2.0-yellow.svg)](https://php.net)
</div>

## 📖 项目介绍

Admin.IM 是开源的网络检测和服务器管理系统。后台及接口基于 MineAdmin 开发， 使用由 Swoole 驱动的 PHP Hyperf 框架，全协程调度 + 异步 I/O 实现，系统性能非常出色，轻松处理大量并发请求。

前端使用 Vue3 + Vite5 + TypeScript + Pinia + Arco Design 开发，自适应多终端。客户端Agent使用 Golang 1.22 开发，支持Linux、Windows、MacOS等多平台运行。

## ✨ 系统特点

- 🔥 **高性能架构**: 基于 Swoole 的 Hyperf 框架，全协程异步实现
- 🎨 **现代化界面**: Vue3 + Arco Design，自适应多终端展示
- 🧩 **插件化设计**: ICMP Ping、TCPing，更多插件开发中
- 🌐 **多语言支持**: 内置多语言功能，支持英文、简体中文、繁体中文切换
- ⏺️ **日志审计**: 用户登录、系统操作记录随时查询
- 🛡️ **稳定可靠**: 经过严格测试，适合生产环境部署
- 🖥️ **跨平台支持**: Agent 支持 Linux、Windows、MacOS

## 📦 仓库地址
- 服务端 Admin.IM：[Github](https://github.com/AdmUU/Admin.IM) | [Gitee](https://gitee.com/AdmUU/Admin.IM)
- 客户端 Adm-Agent：[Github](https://github.com/AdmUU/adm-agent) | [Gitee](https://gitee.com/admuu/adm-agent)
- 前端 UI Adm-Frontend-User：[Github](https://github.com/AdmUU/adm-frontend-user) | [Gitee](https://gitee.com/admuu/adm-frontend-user)
- 后台 UI Adm-Frontend-Admin：[Github](https://github.com/AdmUU/adm-frontend-admin) | [Gitee](https://gitee.com/admuu/adm-frontend-admin)

## 🚀 项目安装

### 环境需求

- Swoole >= 5.0 ，关闭 `Short Name`
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
- Redis >= 4.0
- Composer >= 2.x
- Git >= 2.x

### 下载项目及依赖

```bash
# 克隆项目
git clone https://github.com/AdmUU/Admin.IM.git

# 安装依赖
cd Admin.IM
composer install

# 复制配置文件
cp .env.example .env
```

### 配置安装

配置 `.env` ，填写MySQL数据库、Redis连接等信息，执行安装命令：

```shell
#安装数据库和插件
php bin/hyperf.php mine:install
```
启动服务

```bash
php bin/hyperf.php start
```

## 🐳 Docker部署

线上环境推荐使用 docker compose 方式一键部署。

### 前置条件
- 安装 Docker 和 Docker Compose 插件
- 安装MySQL、Redis（可选）

### 快速部署

1. 创建部署目录并进入：
```bash
mkdir admin-im && cd admin-im
```

2. 创建环境配置文件 .env：

```properties
#.env
ADM_DB_HOST=mysql
ADM_DB_PORT=3306
ADM_DB_USERNAME=user
ADM_DB_PASSWORD=password
ADM_DB_DATABASE=db_name
ADM_REDIS_HOST=redis
ADM_REDIS_PORT=6379
ADM_REDIS_PASSWORD=redis_password
ADM_PORT_HTTP=8090

# ADM_DB_ROOT_PASSWORD=admmysqlrootpwd
```

如果没有事先安装MySQL和Redis，将上面的 ADM_DB_ROOT_PASSWORD 配置项取消注释，设置好MySQL Root密码，部署时由docker自动安装。相关数据库的数据已经持久化，可直接使用。

3. 创建 docker-compose.yml：
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
      - ./data/runtime:/opt/www/runtime
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

4. 启动服务：
```bash
# 使用外部数据库，直接启动
docker compose up -d

# 使用内置数据库，同步安装mysql和redis
docker compose --profile mysql --profile redis up -d
```

5. 查看默认密码：
```bash
docker logs admin-im | grep "Default password"
```

6. 访问系统：
```bash
#前台
http://localhost:8090

#后台
http://localhost:8090/manage/
```

## 📚 文档

详细系统说明请访问 [项目文档](https://doc.admin.im)

## 📝 开源协议

本项目采用 Apache License Version 2.0 协议，详情请参阅 [LICENSE](LICENSE) 文件。

## 🙏 致谢

[MineAdmin](https://www.mineadmin.com)

ljk123/captcha

[Hyperf](https://hyperf.io)

[Swoole](https://www.swoole.com)

[Vue](https://vuejs.org/)

[Arco](https://arco.design/vue)

[Vite](https://vite.dev)