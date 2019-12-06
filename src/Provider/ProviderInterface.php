<?php


namespace Moln\IpQuery\Provider;


interface ProviderInterface
{
    public function query(string $ip, array $context = []): array;
}