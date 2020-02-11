<?php

namespace Moln\IpQuery\Provider;

use GeoIp2\Database\Reader;
use GeoIp2\ProviderInterface as GeoIPProvider;
use Moln\IpQuery\Filter\ProvinceFilter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GeoIp implements ProviderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var GeoIPProvider
     */
    private $geoipProvider;

    public function __construct(GeoIPProvider $reader, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
        $this->geoipProvider = $reader;
    }

    public function query(string $ip, array $context = []): array
    {
        $logger = $this->logger;
        $lang = $context['lang'] ?? 'zh-CN';
        $result = [
            'country'  => '',
            'province' => '',
            'city'     => '',
            'info'     => null,
        ];

        $reader = $this->geoipProvider;
        $ret = $reader->city($ip);

        $city['zh-CN'] =
            isset($ret->city->names['zh-CN']) ? $ret->city->names['zh-CN'] : '';
        $city['en'] =
            isset($ret->city->names['en']) ? $ret->city->names['en'] : '';

        $special = [
            'Hong Kong' => [
                'zh-CN' => [
                    'country'  => '中国',
                    'province' => '香港',
                    'city'     => $city['zh-CN'],
                    'info'     => null,
                ],
                'en'    => [
                    'country'  => 'China',
                    'province' => 'Hong Kong',
                    'city'     => $city['en'],
                    'info'     => null,
                ],
            ],
            'Taiwan'    => [
                'zh-CN' => [
                    'country'  => '中国',
                    'province' => '台湾',
                    'city'     => $city['zh-CN'],
                    'info'     => null,
                ],
                'en'    => [
                    'country'  => 'China',
                    'province' => 'Taiwan',
                    'city'     => $city['en'],
                    'info'     => null,
                ],
            ],
            'Macao'     => [
                'zh-CN' => [
                    'country'  => '中国',
                    'province' => '澳门',
                    'city'     => $city['zh-CN'],
                    'info'     => null,
                ],
                'en'    => [
                    'country'  => 'China',
                    'province' => 'Macao',
                    'city'     => $city['en'],
                    'info'     => null,
                ],
            ],
        ];

        if (isset($special[$ret->country->names['en']])) {
            $result = $special[$ret->country->names['en']][$lang];
        } else {
            if (! empty($ret->country->names[$lang])) {
                $result['country'] = $ret->country->names[$lang];
                if ($result['country'] == '大韩民国') {
                    $result['country'] = '朝鲜';
                }
            }

            if (! empty($ret->city->names[$lang])) {
                $result['city'] = $ret->city->names[$lang];
            }

            if (! empty($ret->subdivisions[0]->names[$lang])) {
                $province = $ret->subdivisions[0]->names[$lang];
                if ($province == '闽') {
                    $province = '福建';
                } elseif ($matched = ProvinceFilter::filter($province)) {
                    $province = $matched;
                } elseif ($result['country'] == '中国') {
                    $logger->warning('GeoIP empty province ' . $province . ', IP: ' . $ip);
                }

                $result['province'] = $province;
            }
        }

        return $result;
    }
}