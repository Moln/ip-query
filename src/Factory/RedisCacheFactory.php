<?php


namespace Moln\IpQuery\Factory;


use Moln\IpQuery\Cache\LocaleSerializer;
use Moln\IpQuery\Cache\RedisCache;
use Psr\Container\ContainerInterface;

class RedisCacheFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new RedisCache(
            $container->get('redis'),
            $container->get(LocaleSerializer::class)
        );
    }
}
