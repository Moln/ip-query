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
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$args = getopt('d');
$daemonize = array_key_exists('d', $args);

$workerNum = $_ENV['WORKER_NUM'] ?? swoole_cpu_num();
$workerMode = $_ENV['WORKER_MODE'] ?? ($workerNum == 1 ? SWOOLE_BASE : SWOOLE_PROCESS);

$config = [
    'dependencies' => [
        'factories' => [
            LoggerInterface::class => function () use ($daemonize) {
                $writers = [
                    [
                        'name'    => 'stream',
                        'options' => [
                            'stream'    => __DIR__ . '/../data/logs/app.log',
                            'formatter' => [
                                'name'    => 'simple',
                                'options' => ['dateTimeFormat' => '[Y-m-d H:i:s]'],
                            ],
                        ],
                    ]
                ];
                if (!$daemonize) {
                    $writers[] = [
                        'name'    => 'stream',
                        'options' => [
                            'stream'    => 'php://stdout',
                            'formatter' => [
                                'name'    => 'simple',
                                'options' => ['dateTimeFormat' => '[Y-m-d H:i:s]'],
                            ],
                        ],
                    ];
                }
                $zendLogLogger = new Laminas\Log\Logger([
                    'writers' => $writers,
                ]);
                $zendLogLogger->addProcessor(new Laminas\Log\Processor\PsrPlaceholder);

                return new Laminas\Log\PsrLoggerAdapter($zendLogLogger);
            },
            BaiduIp::class => ReflectionBasedAbstractFactory::class,
            GeoIp::class => GeoipProviderFactory::class,
            ChainProvider::class => function (ContainerInterface $container) {
                return new ChainProvider(
                    [
                        ['provider' => $container->get(BaiduIp::class), 'allow_countries' => ['中国', '本地局域网']],
//                        $container->get(IpipNetFreeApi::class),
                        $container->get(GeoIp::class),
                    ],
                    $container->get(LoggerInterface::class)
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
                                    ['provider' => $container->build(BaiduIp::class), 'allow_countries' => ['中国', '本地局域网']],
//                                    $container->build(IpipNetFreeApi::class),
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
    'redis' => $_ENV['REDIS'] ?? 'tcp://redis_server',
    'server_redis' => [
        'host' => '0.0.0.0',
        'port' => 9501,
        'mode' => $workerMode,
        'sock_type' => SWOOLE_SOCK_TCP,
        'pid_file' => __DIR__ . '/../data/redis.pid',
        'worker_num' => $workerNum,
        'daemonize' => $daemonize,
    ],
    'server_http' => [
        'host' => '0.0.0.0',
        'port' => 9502,
        'mode' => $workerMode,
        'sock_type' => SWOOLE_SOCK_TCP,
        'pid_file' => __DIR__ . '/../data/http.pid',
        'log_level' => SWOOLE_LOG_DEBUG,
        'worker_num' => $workerNum,
        'open_tcp_keepalive' => 1,
        'tcp_keepidle' => 5, //4s没有数据传输就进行检测
        'tcp_keepinterval' => 1, //1s探测一次
        'tcp_keepcount' => 5,
        'heartbeat_idle_time'      => 10, // 表示一个连接如果10秒内未向服务器发送任何数据，此连接将被强制关闭
        'heartbeat_check_interval' => 5,  // 表示每5秒遍历一次
        'daemonize' => $daemonize,
    ],
];
$container = new ServiceManager($config['dependencies']);
$container->setService('config', $config);

function config_pop(array &$config, string $key) {
    $value = $config[$key];
    unset($config[$key]);

    return $value;
}

return $container;