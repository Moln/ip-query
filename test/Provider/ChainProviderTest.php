<?php

namespace MolnTest\IpQuery\Provider;

use Moln\IpQuery\Provider\ChainProvider;
use MolnTest\IpQuery\ContainerTrait;
use PHPUnit\Framework\TestCase;

class ChainProviderTest extends TestCase
{
    use ContainerTrait;

    public function testQuery()
    {
        $provider = $this->container->get(ChainProvider::class);
        $result = $provider->query('103.63.155.5');

        $this->assertKeyExists($result);
    }
}
