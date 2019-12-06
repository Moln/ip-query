<?php


namespace Moln\IpQuery\Provider;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class IpipNet
 *
 * @see https://www.ipip.net/ip.html
 */
class IpipNetFreeApi implements ProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $url = 'http://freeapi.ipip.net/';

    /**
     * @var Client
     */
    private $client;

    public function __construct(ClientInterface $client = null, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
        $this->client = $client ?: new Client(['connect_timeout' => 5, 'timeout' => 5]);
    }

    public function query(string $ip, array $context = []): array
    {
//    $url = 'https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=8.8.8.8&co=&resource_id=6006&t=1511147699339&ie=utf8&oe=utf8&cb=op_aladdin_callback&format=json&tn=baidu&cb=jQuery110206850772970366219_1511147682169&_=1511147682171';

        $header = [
            'Accept-Language' => 'zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
            'Cookie' => '__jsluid_h=9a1172beb85c2e9095b80a75f9932328',
        ];

        try {
            $response = $this->client->get($this->url . $ip, [
                'headers' => $header,
            ]);
        } catch(\Exception $e) {
            throw new \RuntimeException(sprintf('IP(%s), ipip.net request error: %s', $ip, $e->getMessage()), 0, $e);
        }

        if ($response->getStatusCode() != 200) {
            $msg = sprintf(
                'IP(%s) ipip.net Response error, %s,%s',
                $ip,
                $response->getStatusCode(),
                $response->getBody()
            );
            throw new \RuntimeException($msg);
        }

        $body = (string)$response->getBody();
        $res = json_decode($body, true);

        if (! is_array($res)) {
            throw new \RuntimeException('freeapi.ipip.net response error, ' . $body);
        }

        $result = [
            'country' => $res[0],
            'province' => $res[1],
            'city' =>  $res[2],
            'info' =>  $res[3] . ($res[3] ? ' ' : '') . ($res[4] ?? ''),
        ];

        return $result;
    }
}