<?php

namespace MolnTest\IpQuery\Provider;

use Moln\IpQuery\Provider\BaiduIp;
use MolnTest\IpQuery\ContainerTrait;
use PHPUnit\Framework\TestCase;

class BaiduIpTest extends TestCase
{
    use ContainerTrait;

    public function testQuery()
    {
        $provider = new BaiduIp();
        $result = $provider->query('103.63.155.5');

        $this->assertKeyExists($result);
    }
}
