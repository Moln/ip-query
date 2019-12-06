<?php

$redis = new Redis();
$redis->connect('192.168.2.152', 9501);
//$redis->connect('192.168.2.152', 6379);
//$redis->set('103.63.155.5', str_repeat('a', 1024));
var_dump($redis->get('103.63.155.5'));
//exit;

$s = microtime(true);
for ($i = 0; $i < 10000; $i ++) {
    $redis->get('103.63.155.5');
}

$e = microtime(true);

echo "\n";
echo $e, ' - ', $s, ' = ', $e-$s;
