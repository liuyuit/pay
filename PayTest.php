<?php

require_once __DIR__ . '/vendor/autoload.php';

use liuyuit\XyPaySdk\Pay;

error_reporting(E_ALL);
ini_set('display_errors', 'On');


$config = [
    'app_id' => '2017031406209437',
    'notify_url' => 'http://yansongda.cn/notify.php',
    'return_url' => 'http://yansongda.cn/return.php',
    'ali_public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrI',
    // 加密方式： **RSA**
    'private_key' => 'MIICXQIBAAKBgQDdTHJO+Wi+x0vRWA7P14',
    // 使用公钥证书模式，请配置下面两个参数，同时修改ali_public_key为以.crt结尾的支付宝公钥证书路径，如（./cert/alipayCertPublicKey_RSA2.crt）
    // 'app_cert_public_key' => './cert/appCertPublicKey.crt', //应用公钥证书路径
    // 'alipay_root_cert' => './cert/alipayRootCert.crt', //支付宝根证书路径
/*    'log' => [ // optional
        'file' => './logs/alipay.log',
        'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
        'type' => 'single', // optional, 可选 daily.
        'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
    ],
    'http' => [ // optional
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
        // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
    ],
    'mode' => 'dev', // optional,设置此参数，将进入沙箱模式*/
    'sign_type' => 'RSA',
];

$order = [
    'out_trade_no' => time(),
    'total_amount' => '1',
    'subject' => 'testsubject',
];
try {
    $alipay = Pay::alipay($config)->web($order);
} catch (\Exception $exception) {
    var_dump($exception->getMessage());
}

echo $alipay->send();// laravel 框架中请直接 `return $alipay`

