<?php

namespace MolnTest\IpQuery\Provider;

use Moln\IpQuery\Provider\IpipNetFreeApi;
use PHPUnit\Framework\TestCase;

class IpipNetFreeApiTest extends TestCase
{

    public function testQuery()
    {
        $provider = new IpipNetFreeApi();
        $result = $provider->query('103.63.155.5');

        $this->assertEquals(
            [
                'country' => '中国',
                'province' => '辽宁',
                'city' => '营口',
                'info' => '鹏博士',
            ],
            $result
        );
    }
}
