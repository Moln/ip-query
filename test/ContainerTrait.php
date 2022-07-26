<?php


namespace MolnTest\IpQuery;


use Laminas\ServiceManager\ServiceManager;

trait ContainerTrait
{
    /** @var ServiceManager */
    private $container;

    protected function setUp(): void
    {
        $this->container = include __DIR__ . '/../config/container.php';
    }

    public function assertKeyExists(array $result): void
    {
        $this->assertArrayHasKey('country', $result);
        $this->assertArrayHasKey('province', $result);
        $this->assertArrayHasKey('city', $result);
        $this->assertArrayHasKey('info', $result);
    }
}