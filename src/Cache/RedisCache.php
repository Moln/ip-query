<?php


namespace Moln\IpQuery\Cache;


use Psr\SimpleCache\CacheInterface;

/**
 * 用Redis存储数据, 并序列化压缩数据
 */
class RedisCache implements CacheInterface
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var LocaleSerializer
     */
    private $serializer;

    private $defaultTtl = 86400 * 30;


    /**
     * RedisSimpleCache constructor.
     *
     * @param \Redis|\RedisArray|\RedisCluster $redis
     * @param LocaleSerializer                 $serializer
     * @param int                              $defaultTtl
     */
    public function __construct($redis, LocaleSerializer $serializer, int $defaultTtl = 86400 * 30)
    {
        if (!($redis instanceof \Redis || $redis instanceof \RedisArray || $redis instanceof \RedisCluster)) {
            throw new \InvalidArgumentException('Invalid redis argument.');
        }

        $this->serializer = $serializer;
        $this->redis = $redis;
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $rs = $this->redis->get($key);

        if ($rs) {
            return $this->serializer->unserialize($rs);
        }

        return $rs;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $str = $this->serializer->serialize($value);
        $this->redis->set($key, $str, $ttl ?: $this->defaultTtl);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        $this->redis->del($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        while ($keys = $this->redis->scan($it, '*')) {
            $this->redis->del($keys);
        }
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        foreach ($keys as $key) {
            yield $this->get($key);
        }
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return (bool) $this->redis->exists($key);
    }
}
