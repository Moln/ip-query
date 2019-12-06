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

    public function testAllowCountries()
    {
        $provider = $this->container->get(ChainProvider::class);
        $result = $provider->query('70.165.110.26');
//        $result = $provider->query('49.174.149.20');

        $this->assertEquals(
            [
                'country' => '美国',
                'province' => '内布拉斯加州',
                'city' => '贝尔维尤',
                'info' => 'cox.com',
            ],
            $result
        );
    }
}
