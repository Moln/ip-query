# ip-query
基于 Redis 协议的IP查询库

## 安装PHP依赖

```bash
composer install --no-dev -o -n
```

## 服务启动

### 使用 docker-compose

#### 内包含 crontab 定期执行 geoipupdate

- 若需要执行 geoip 自动更新（geoipupdate）， 需要填写 [`docker/GeoIP.conf`](docker/GeoIP.conf) 的配置 
  - `AccountID` 
  - `LicenseKey`
- crontab 配置文件：[`docker/geoipupdate-cron`](docker/geoipupdate-cron)

#### 启动：

```bash
docker-compose up -d
```

`.env` 支持的环境参数：
```bash
REDIS=<REDIS_HOST>:<REDIS_PORT>
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
