<?php


namespace Moln\IpQuery\Cache;


/**
 * 序列化存储数据
 * 映射国家省份数据, 减少数据存储大小
 */
class LocaleSerializer
{
    const SEPARATOR = '/';

    /** @var array  */
    private $countries;

    /**
     * @var array
     */
    private $provinces;
    /**
     * @var array
     */
    private $countries2;
    /**
     * @var array
     */
    private $provinces2;

    public function __construct(array $countries, array $provinces = [])
    {
        $this->countries = $countries;
        $this->countries2 = array_flip($countries);
        $this->provinces = $provinces;

        foreach ($provinces as $country => $subProvinces) {
            $this->provinces2[$country] = array_flip($subProvinces);
        }
    }

    public function serialize(array $data): string
    {
        $country = $this->countries[$data['country']] ?? $data['country'];
        $province = $this->provinces[$data['country']][$data['province']] ?? $data['province'];

        return $country . self::SEPARATOR . $province . self::SEPARATOR . $data['city'] . self::SEPARATOR . $data['info'];
    }

    public function unserialize(string $serialized): array
    {
        $result = [];
        [$result['country'], $result['province'], $result['city'], $result['info']] =
            explode(self::SEPARATOR, $serialized);

        if (isset($this->countries2[$result['country']])) {
            $result['country'] = $this->countries2[$result['country']];
        }

        if (isset($this->provinces2[$result['country']][$result['province']])) {
            $result['province'] = $this->provinces2[$result['country']][$result['province']];
        }

        return $result;
    }
}