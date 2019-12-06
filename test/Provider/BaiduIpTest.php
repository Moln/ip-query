<?php

namespace MolnTest\IpQuery\Provider;

use Moln\IpQuery\Provider\BaiduIp;
use PHPUnit\Framework\TestCase;

class BaiduIpTest extends TestCase
{

    public function testQuery()
    {
        $provider = new BaiduIp();
        $result = $provider->query('103.63.155.5');

        $this->assertEquals(
            [
                'country' => '中国',
                'province' => '辽宁',
                'city' => '营口',
                'info' => '长城宽带',
            ],
            $result
        );
    }


    public function testQueryCountry()
    {
        $provider = new BaiduIp();
        $result = $provider->query('204.16.188.4');

        $this->assertEquals(
            [
                'country' => '加拿大',
                'province' => '',
                'city' => '',
                'info' => '蒙特利尔',
            ],
            $result
        );
    }
}
