<?php

include_once  __DIR__ . '/../vendor/autoload.php';

use Moln\IpQuery\Cache\LocaleSerializer;
use Moln\IpQuery\Cache\RedisCache;
use Moln\IpQuery\Factory\GeoipProviderFactory;
use Moln\IpQuery\Factory\LocaleSerializerFactory;
use Moln\IpQuery\Factory\RedisCacheFactory;
use Moln\IpQuery\Factory\RedisFactory;
use Moln\IpQuery\Provider\BaiduIp;
use Moln\IpQuery\Provider\CacheWrapperProvider;
use Moln\IpQuery\Provider\ChainProvider;
use Moln\IpQuery\Provider\GeoIp;
use Moln\IpQuery\Provider\IpipNetFreeApi;
use Moln\IpQuery\Provider\ProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Zend\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Zend\ServiceManager\ServiceManager;

$config = [
    'dependencies' => [
        'factories' => [
            LoggerInterface::class => function () {
                $zendLogLogger = new Zend\Log\Logger([
                    'writers' => [
                        [
                            'name'    => 'stream',
                            'options' => [
                                'stream'    => __DIR__ . '/../data/logs/app.log',
                                'formatter' => [
                                    'name'    => 'simple',
                                    'options' => ['dateTimeFormat' => '[Y-m-d H:i:s]'],
                                ],
                            ],
                        ],
                        [
                            'name'    => 'stream',
                            'options' => [
                                'stream'    => 'php://stdout',
                                'formatter' => [
                                    'name'    => 'simple',
                                    'options' => ['dateTimeFormat' => '[Y-m-d H:i:s]'],
                                ],
                            ],
                        ],
                    ],
                ]);
                $zendLogLogger->addProcessor(new Zend\Log\Processor\PsrPlaceholder);

                return new Zend\Log\PsrLoggerAdapter($zendLogLogger);
            },
            BaiduIp::class => ReflectionBasedAbstractFactory::class,
            GeoIp::class => GeoipProviderFactory::class,
            ChainProvider::class => function (ContainerInterface $container) {
                return new ChainProvider(
                    [
                        ['provider' => $container->get(BaiduIp::class), 'allow_countries' => ['中国']],
                        $container->get(IpipNetFreeApi::class),
                        $container->get(GeoIp::class),
                    ],
                    $container->get(LoggerInterface::class),
                );
            },
            IpipNetFreeApi::class => ReflectionBasedAbstractFactory::class,
            LocaleSerializer::class => LocaleSerializerFactory::class,
            CacheWrapperProvider::class => ReflectionBasedAbstractFactory::class,
            RedisCache::class => RedisCacheFactory::class,
            \Redis::class => RedisFactory::class,
            'ProviderPool' => function (ServiceManager $container) {
                return new \Swoole\ConnectionPool(
                    function () use ($container) {
                        return new CacheWrapperProvider(
                            new ChainProvider(
                                [
                                    ['provider' => $container->build(BaiduIp::class), 'allow_countries' => ['中国']],
                                    $container->build(IpipNetFreeApi::class),
                                    $container->build(GeoIp::class)
                                ],
                                $container->get(LoggerInterface::class)
                            ),
                            new RedisCache(
                                $container->build('redis'),
                                $container->get(LocaleSerializer::class)
                            )
                        );
                    },
                    20
                );
            }
        ],
        'aliases' => [
            'redis' => \Redis::class,
            ProviderInterface::class => ChainProvider::class,
            CacheInterface::class => RedisCache::class,
        ]
    ],
    'ip-query' => [
        'path' => __DIR__ . '/../data/GeoLite2-City.mmdb',
    ],
    'redis' => 'tcp://192.168.2.152'
];
$container = new ServiceManager($config['dependencies']);
$container->setService('config', $config);


return $container;