<?php


namespace Moln\IpQuery\Factory;


use Psr\Container\ContainerInterface;

class RedisFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $redisConfig = $container->get('config')['redis'];
        $redis = new \Redis();

        if (is_string($redisConfig)) {
            $redisConfig = parse_url($redisConfig);
            if (isset($redisConfig['query'])) {
                parse_str($redisConfig['query'], $params);
                $redisConfig = $redisConfig + $params;
            }
        }

        $redis->connect($redisConfig['host'], $redisConfig['port'] ?? 6379, $redisConfig['timeout'] ?? 0);

        if (isset($redisConfig['path'])) {
            $redis->select((int)$redisConfig['path']);
        }

        return $redis;
    }
}