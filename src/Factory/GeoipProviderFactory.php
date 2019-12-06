<?php
namespace Moln\IpQuery\Factory;

use GeoIp2\Database\Reader;
use Moln\IpQuery\Provider\GeoIp;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class GeoipProviderFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['ip-query'] ?? null;
        if (! $config || empty($config['path'])) {
            throw new class('Config key "ip-query" was empty.')
                extends RuntimeException
                implements NotFoundExceptionInterface {
            };
        }

        return new GeoIp(
            new Reader($container->get('config')['ip-query']['path'], $config['locales'] ?? ['en']),
            $container->has(LoggerInterface::class) ? $container->get(LoggerInterface::class) : null,
        );
    }
}