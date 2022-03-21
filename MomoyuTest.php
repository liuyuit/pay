<?php

require_once __DIR__.'/vendor/autoload.php';

use liuyuit\XyPaySdk\Pay;

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$config = [
    'app_id' => '2017031406209437',
    'notify_url' => 'http://yansongda.cn/notify.php',
    'return_url' => 'http://yansongda.cn/return.php',
    'public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDmJHw+qP7vzf+QyL2/AC+dItwDMcoClG5csqiUgL6vl4801HogkBGHI9eQZuFqwJKJENoIkPA5apuitGJIor4CaomqiuWHMY+oIywPP+hXelcxzVDf8nx8XWe2f8WVbHlfI9EL40cvcijl5xv2xXGfxqFwvWzbC+64nszfD1HiewIDAQAB',
    'pay_key' => 'MIICXQIBAAKBgQDdTHJO+Wi+x0vRWA7P14',
];

$order = [
    'out_trade_no' => time(),
    'total_amount' => '1',
    'subject' => 'testsubject',
    'uid' => 12,
];
try {
    $alipay = Pay::momoyu($config)->app($order);
} catch (\Exception $exception) {
    var_dump($exception->getMessage());
}

echo $alipay->send(); // laravel 框架中请直接 `return $alipay`

echo "\r\n";

$req_url_params = [
    'a' => 'b',
    'sign' => 'k311FAvUjloiqMaywPvJ+q2M76pkcPr1KrDWAwE05TWEvrITjU1PVJ2hCT+rrnsSqQQFs54ulXEKQG+Bc5Geq4w5zIojWTKyd30yE9J2OvExQgQZoaoiBD7zH1pErk3uxRy03JBw9e8E8y5QsicHmDMgfwxUfNLMBO4uUGc3YIM=',
];
$result = Pay::momoyu($config)->verify($req_url_params);
var_dump($result);



