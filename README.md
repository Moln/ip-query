# ip-query
基于 Redis 协议的IP查询库

## 安装PHP依赖

```bash
composer install --no-dev -o -n
```

## 服务启动

### 使用 docker-compose

内包含 crontab 定期执行 geoipupdate，需要配置环境参数 `GEOIPUPDATE_ACCOUNT_ID`, `GEOIPUPDATE_LICENSE_KEY`


#### Docker 支持的环境参数：
```bash
REDIS=<REDIS_HOST>:<REDIS_PORT>
WORKER_NUM=2 # 启动进程数，1 个 CPU数， 一个进程数
GEOIPUPDATE_ACCOUNT_ID=<YOUR_ACCOUNT_ID_HERE>   # 必须
GEOIPUPDATE_LICENSE_KEY=<YOUR_LICENSE_KEY_HERE> # 必须
```

#### 启动：

```bash
docker-compose up -d
```

### 基于 HTTP 服务:

```
php ./bin/serve-http
```

#### 使用

```
curl "http://localhost:9502/?ip=114.114.114.114"
```

### 基于 Redis 服务:

```
php ./bin/serve-http
```

#### 使用

```
redis-cli --raw -p 9501 GET "114.114.114.114"
```

### 定期更新 GeoIP 

```
0 13 * * 1 /PATH/TO/ip-query/bin/geoipupdate.sh
```
