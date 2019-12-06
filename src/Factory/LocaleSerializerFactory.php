<?php


namespace Moln\IpQuery\Factory;


use Moln\IpQuery\Cache\LocaleSerializer;
use Moln\IpQuery\Filter\CountryFilter;
use Moln\IpQuery\Filter\ProvinceFilter;
use Psr\Container\ContainerInterface;

class LocaleSerializerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $countries = [];
        foreach (CountryFilter::COUNTRY_DICT as $item) {
            $countries[$item[1]] = $item[3];
        }
        return new LocaleSerializer($countries, ['中国' => array_flip(ProvinceFilter::PROVINCES)]);
    }
}