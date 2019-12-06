<?php


namespace Moln\IpQuery\Filter;


class ProvinceFilter
{
    const PROVINCES = [
        '北京',
        '天津',
        '上海',
        '重庆',
        '河北',
        '山西',
        '辽宁',
        '吉林',
        '黑龙江',
        '江苏',
        '浙江',
        '安徽',
        '福建',
        '江西',
        '山东',
        '河南',
        '湖北',
        '湖南',
        '广东',
        '海南',
        '四川',
        '贵州',
        '云南',
        '陕西',
        '甘肃',
        '青海',
        '台湾',
        '内蒙古',
        '广西',
        '西藏',
        '宁夏',
        '新疆',
        '香港',
        '澳门'
    ];
    const PATTERN = '(北京|天津|上海|重庆|河北|山西|辽宁|吉林|黑龙江|江苏|浙江|安徽|福建|江西|山东|河南|湖北|湖南|广东|海南|四川|贵州|云南|陕西|甘肃|青海|台湾|内蒙古|广西|西藏|宁夏|新疆|香港|澳门)';

    public static function filter($result)
    {
        if (preg_match(self::PATTERN, $result, $m)) {
            return $m[0];
        } else {
            return null;
        }
    }
}