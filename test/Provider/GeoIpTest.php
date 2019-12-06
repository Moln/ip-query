<?php

namespace MolnTest\IpQuery\Provider;

use Moln\IpQuery\Provider\GeoIp;
use MolnTest\IpQuery\ContainerTrait;
use PHPUnit\Framework\TestCase;

class GeoIpTest extends TestCase
{
    use ContainerTrait;

    public function testQuery()
    {
        /** @var GeoIp $provider */
        $provider = $this->container->get(GeoIp::class);
        $result = $provider->query('103.63.155.5');
//        $result = $provider->query('113.159.192.27');

        $this->assertEquals(
            [
                'country' => '中国',
                'province' => '北京',
                'city' => '',
                'info' => null,
            ],
            $result
        );
    }
}
