<?php

namespace MolnTest\IpQuery\Provider;

use Moln\IpQuery\Provider\CacheWrapperProvider;
use MolnTest\IpQuery\ContainerTrait;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class CacheWrapperProviderTest extends TestCase
{
    use ContainerTrait;

    public function testQuery()
    {
        /** @var \Redis $redis */
        $redis = $this->container->get('redis');
        $redis->select(1);
        $cache = $this->container->get(CacheInterface::class);
        $cache->clear();

        $provider = $this->container->get(CacheWrapperProvider::class);
        $result = $provider->query('103.63.155.5');
        $this->assertKeyExists($result);

        //From cache
        $result = $provider->query('103.63.155.5');
        $this->assertKeyExists($result);
    }
}
