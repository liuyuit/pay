<?php

require_once __DIR__ . '/vendor/autoload.php';

use liuyuit\Pay\Pay;

error_reporting(E_ALL);
ini_set('display_errors', 'On');


$config = [
    'app_id' => '2017031406209437',
    'notify_url' => 'http://yansongda.cn/notify.php',
    'return_url' => 'http://yansongda.cn/return.php',
    'ali_public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB',
    // 加密方式： **RSA**
    'private_key' => 'MIICXQIBAAKBgQDdTHJO+Wi+x0vRWA7P14PQlzoB1U9YtA0qYP2o/LBV27onLtS2NU7kNsvEhlMe+i5GY4pHe4vG4mR5OfFd22nsiZtVim+MoocaKysPSFhSgNnfdUg51nSOda+SaE0sZQorlOCz9sNvALaYy7yjvcog1Ys8nXweBJ8cpTJrSHn/lwIDAQABAoGBAKwCS6dO/TesuSXTCFBM98wef5wFLVOJP+J82S2Mm5Ng4uSp5fRqoxOH9AKhVeJyG53iqQy+3vqL5gTEIPQPI9ihV8jQ0lMjYegqRbmqLdqwEeMpxFv/fss6m5tb4W3aQlgO1x3V1wf9yWcqrUwT/0+HzVRv9iA/5Ua2l0cy9d4BAkEA8qUhqwNIGZO7sgnfCWc3USJXKRKvRpoWhp+xLnOBwk66RYBWFmN9GqAabZTtdr1fwFzZ52pHAD7d7E6Peqq6VwJBAOl6izu8ajfAy2KlYUug70s72SFA3rXAe6AlqRqB1PO+GsPAZBVMmPaLYecg3QxGP6E6YqT4VE2eyiF9xXBuHMECQHLIPucBBGhlBFMybDmsg/RzhDu/ xP5nAeTpQg2xQl7Ck0cxcIfixFmGBzpzSunyp4r94W6hTbkGBEE24JSskr0CQDkzRJlF99/g4/MvAT8+FmIgL3nuIqH0nlUF5QARftKYaIY8xEhTk8YTL9EoQ/+V1lDxtOklzcGWfX7nCVAaKgECQQCWIW9gzSXE2CR/A+zp5Vs3R6glMZWQltpr9XSoe1bhZ8JJ43e6P+urmsJEEwJ89phnYGtWrhGs7YyNcKpnnD9J',
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

