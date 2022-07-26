<?php

namespace MolnTest\IpQuery\Provider;

use Moln\IpQuery\Provider\IpipNetFreeApi;
use MolnTest\IpQuery\ContainerTrait;
use PHPUnit\Framework\TestCase;

class IpipNetFreeApiTest extends TestCase
{
    use ContainerTrait;

    public function testQuery()
    {
        $provider = new IpipNetFreeApi();
        $result = $provider->query('103.63.155.5');

        $this->assertKeyExists($result);
    }
}
