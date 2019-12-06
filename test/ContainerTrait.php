<?php


namespace MolnTest\IpQuery;


use Zend\ServiceManager\ServiceManager;

trait ContainerTrait
{
    /** @var ServiceManager */
    private $container;

    protected function setUp(): void
    {
        $this->container = include __DIR__ . '/../config/container.php';
    }
}