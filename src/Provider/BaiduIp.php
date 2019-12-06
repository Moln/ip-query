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
        $this->client = $client ?: new Client(['connect_timeout' => 5, 'timeout' => 5]);
    }

    public function query(string $ip, array $context = []): array
    {
        $url = 'https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php';
        $params = [
            'query' => $ip,
            'co' => '',
            'resource_id' => '6006',
            't' => intval(microtime(true) * 1000),
            'ie' => 'utf8',
            'oe' => 'utf8',
            //        'cb' => 'op_aladdin_callback',
            'format' => 'format',
            'tn' => 'baidu',
            'cb' => 'jQuery11020685077' . mt_rand(1000000000, 9999999999) . '_' . intval(microtime(true) * 1000),
            '_' => intval(microtime(true) * 1000)
        ];
//    $url = 'https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query=8.8.8.8&co=&resource_id=6006&t=1511147699339&ie=utf8&oe=utf8&cb=op_aladdin_callback&format=json&tn=baidu&cb=jQuery110206850772970366219_1511147682169&_=1511147682171';

        $header = [
            'Accept-Language' => 'zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0',
            'Referer' => 'https://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=0&rsv_idx=1&tn=baidu&wd=ip&rsv_pq=ba77603200006cd3&rsv_t=f78bABtiVUsMf5KhHXgZjfFntj2xrvmzqdtbx9r7yDSNAvNAojeCPgTVkFk&rqlang=cn&rsv_enter=1&rsv_sug3=3&rsv_sug1=2&rsv_sug7=100&rsv_sug2=0&inputT=606&rsv_sug4=1273',
            'Cookie' => 'BAIDUID=19A6B4EE4548062BBD3996F886148BD5:FG=1; BIDUPSID=19A6B4EE4548062BBD3996F886148BD5; PSTM=1493809988; H_PS_PSSID=1464_21078_18559',
        ];

        try {
            $response = $this->client->get($url, [
                'headers' => $header,
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

        $res = substr($body, 46, -2);
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