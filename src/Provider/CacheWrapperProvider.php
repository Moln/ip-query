<?php


namespace Moln\IpQuery\Provider;


use Psr\SimpleCache\CacheInterface;

class CacheWrapperProvider implements ProviderInterface
{
    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $config;

    /**
     * @var int
     */
    private $netMask;

    public function __construct(ProviderInterface $provider, CacheInterface $cache, int $netMask = 26)
    {
        $this->provider = $provider;
        $this->cache = $cache;
        $this->netMask = (pow(2, $netMask) - 1) << (32 - $netMask);
    }

    public function query(string $ip, array $context = []): array
    {
        $longIp = ip2long($ip);

        $gatewayIP = $longIp & $this->netMask;
        $key = pack('i', $gatewayIP);
        if ($result = $this->cache->get($key)) {
            return $result;
        }

        $result = $this->provider->query($ip, $context);
        $this->cache->set($key, $result);

        return $result;
    }
}
