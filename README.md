# ip-query
基于 Redis 协议的IP查询库


## 服务启动

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
