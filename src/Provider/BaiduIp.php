<?php

namespace Moln\IpQuery\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Moln\IpQuery\Filter\CountryFilter;
use Moln\IpQuery\Filter\ProvinceFilter;

class BaiduIp implements ProviderInterface
{

    /**
     * @var Client
     */
    private $client;

    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client ?: $this->getDefaultClient();
    }

    protected function getDefaultClient()
    {
        $header = [
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36 Edg/107.0.1418.35',
            'Referer' => 'https://www.baidu.com/s?ie=UTF-8&wd=ip',
            'Cookie' => 'BAIDUID=CE4A95DA408907B58FA7706C0896CE72:FG=1; BIDUPSID=72262A597F4566DE15E2BB76898A5FBE; PSTM=1493809988; delPer=0; PSINO=5; H_PS_PSSID=37858_36557_37908_37766_37932_37760_37903_26350_22160_37881;',
        ];

        return new Client([
            'connect_timeout' => 5,
            'timeout' => 5,
            'headers' => $header,
        ]);
    }

    public function query(string $ip, array $context = []): array
    {
        $url = 'https://sp1.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php';
        $params = [
            'query' => $ip,
            'co' => '',
            'resource_id' => '5809',
            't' => intval(microtime(true) * 1000),
            'ie' => 'utf8',
            'oe' => 'utf8',
            // 'cb' => 'op_aladdin_callback',
            'format' => 'json',
            'tn' => 'baidu',
            'cb' => 'jQuery11020685077' . mt_rand(1000000000, 9999999999) . '_' . intval(microtime(true) * 1000),
            '_' => intval(microtime(true) * 1000)
        ];
//    $url = 'https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=8.8.8.8&co=&resource_id=6006&t=1511147699339&ie=utf8&oe=utf8&cb=op_aladdin_callback&format=json&tn=baidu&cb=jQuery110206850772970366219_1511147682169&_=1511147682171';


        try {
            $response = $this->client->get($url, [
                'query' => $params,
            ]);
        } catch(\Exception $e) {
            throw new \RuntimeException(sprintf('IP(%s), IP138 request error: %s', $ip, $e->getMessage()), 0, $e);
        }

        if ($response->getStatusCode() != 200) {
            $msg = sprintf('IP(%s) IP138 Response error, %s,%s', $ip, $response->getStatusCode(), $response->getBody());
            throw new \RuntimeException($msg);
        }

        $result = [
            'country' => '',
            'province' => '',
            'city' => '',
            'info' => null,
        ];

        $body = $response->getBody();

        $res = substr($body, 42, -1);
        $res = json_decode($res, true);

        if ($res['status'] != 0 || !count($res['data'])) {
            throw new \RuntimeException('Baidu IP138 Response error, ' . $body);
        }

        $locale = $res['data'][0]['location'];
        $localeParsed = explode(' ', $locale);
        if (count($localeParsed) > 1) {
            $result['info'] = $localeParsed[1];
            $locale = $localeParsed[0];
        }

        $province = ProvinceFilter::filter($locale);
        if ($province) {
            $result['country'] = '中国';
            $result['province'] = $province;

            $provinces1 = ['北京','天津','上海','重庆'];
            $provinces2 = ['内蒙古','广西','西藏','宁夏','新疆','香港','澳门'];
            if (in_array($province, $provinces1)) {
                $result['city'] = $province;
            } elseif (in_array($province, $provinces2)) {
                $info = explode('区', $locale, 2);
                $result['city'] = $info[1];
            } else {
                $info = explode('省', $locale, 2);
                $result['city'] = $info[1];
            }
        } else {
            $rs = CountryFilter::filter($locale);
            if (! $rs) {
                $result['country'] = $locale;
            } else {
                [$result['country'], $result['info']] = $rs;
            }
        }

        if ($result['city']) {
            $info = explode('市',  $result['city'], 2);
            $result['city'] = $info[0];

            if (!empty($info[1])) {
                $result['info'] = $info[1] . ' ' . $result['info'];
            }
        }

        return $result;
    }
}