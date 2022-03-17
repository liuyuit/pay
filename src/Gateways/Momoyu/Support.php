<?php

namespace liuyuit\Pay\Gateways\Momoyu;

use Exception;
use liuyuit\Pay\Events;
use liuyuit\Pay\Exceptions\GatewayException;
use liuyuit\Pay\Exceptions\InvalidArgumentException;
use liuyuit\Pay\Exceptions\InvalidConfigException;
use liuyuit\Pay\Exceptions\InvalidSignException;
use liuyuit\Pay\Gateways\Momoyu;
use liuyuit\Pay\Log;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;
use Yansongda\Supports\Traits\HasHttpRequest;

/**
 * @author yansongda <me@yansongda.cn>
 *
 * @property string app_id alipay app_id
 * @property string public_key
 * @property string pay_key
 * @property array http http options
 * @property string mode current mode
 * @property array log log options
 * @property string pid ali pid
 */
class Support
{
    use HasHttpRequest;

    /**
     * Alipay gateway.
     *
     * @var string
     */
    protected $baseUri;

    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Instance.
     *
     * @var Support
     */
    private static $instance;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     */
    private function __construct(Config $config)
    {
        $this->baseUri = Momoyu::URL[$config->get('mode', Momoyu::MODE_NORMAL)];
        $this->config = $config;

        $this->setHttpOptions();
    }

    /**
     * __get.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $key
     *
     * @return mixed|Config|null
     */
    public function __get($key)
    {
        return $this->getConfig($key);
    }

    /**
     * create.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Support
     */
    public static function create(Config $config)
    {
        if ('cli' === php_sapi_name() || !(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * getInstance.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws InvalidArgumentException
     *
     * @return Support
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            throw new InvalidArgumentException('You Should [Create] First Before Using');
        }

        return self::$instance;
    }

    /**
     * clear.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function clear()
    {
        self::$instance = null;
    }

    /**
     * Get Momoyu API result.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public static function requestApi(array $data, bool $response = false): Collection
    {
        Events::dispatch(new Events\ApiRequesting('Momoyu', '', self::$instance->getBaseUri(), $data));

        $data = array_filter($data, function ($value) {
            return ('' == $value || is_null($value)) ? false : true;
        });

        $result = json_decode(self::$instance->post('', $data), true);

        Events::dispatch(new Events\ApiRequested('Momoyu', '', self::$instance->getBaseUri(), $result));

        return self::processingApiResult($data, $result, $response);
    }

    /**
     * Generate sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws InvalidConfigException
     */
    public static function generateSign(array $params): string
    {
        $payKey = self::$instance->pay_key;

        if (is_null($payKey)) {
            throw new InvalidConfigException('Missing Momoyu Config -- [pay_key]');
        }
        $signContent = self::getSignContent($params);
        $signContent = $payKey . $signContent . $payKey;
        $sign = md5($signContent);

        Log::debug('Momoyu Generate Sign', [$params, $sign]);

        return $sign;
    }
    /**
     * Verify sign.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param bool        $sync
     * @param string|null $sign
     *
     * @throws InvalidConfigException
     */
    public static function verifySign(array $data, $sync = false, $sign = null): bool
    {
        $publicKey = self::$instance->public_key;

        if (is_null($publicKey)) {
            throw new InvalidConfigException('Missing Momoyu Config -- [ali_public_key]');
        }

        if (Str::endsWith($publicKey, '.crt')) {
            $publicKey = file_get_contents($publicKey);
        } elseif (Str::endsWith($publicKey, '.pem')) {
            $publicKey = openssl_pkey_get_public(
                Str::startsWith($publicKey, 'file://') ? $publicKey : 'file://'.$publicKey
            );
        } else {
            $publicKey = "-----BEGIN PUBLIC KEY-----\n".
                wordwrap($publicKey, 64, "\n", true).
                "\n-----END PUBLIC KEY-----";
        }

        $sign = $sign ?? $data['sign'];

        $toVerify = $sync ? json_encode($data, JSON_UNESCAPED_UNICODE) : self::getSignContent($data, true);

        $isVerify = 1 === openssl_verify($toVerify, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA1);

        if (is_resource($publicKey)) {
            openssl_free_key($publicKey);
        }

        return $isVerify;
    }

    /**
     * Get signContent that is to be signed.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param bool $verify
     */
    public static function getSignContent(array $data, $verify = false): string
    {
        ksort($data);

        $stringToBeSigned = '';
        foreach ($data as $k => $v) {
            if ($verify && 'sign' != $k && 'sign_type' != $k) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
            if (!$verify && '' !== $v && !is_null($v) && 'sign' != $k && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
        }

        Log::debug('Momoyu Generate Sign Content Before Trim', [$data, $stringToBeSigned]);

        return trim($stringToBeSigned, '&');
    }

    /**
     * Convert encoding.
     *
     * @author yansongda <me@yansonga.cn>
     *
     * @param string|array $data
     * @param string       $to
     * @param string       $from
     */
    public static function encoding($data, $to = 'utf-8', $from = 'gb2312'): array
    {
        return Arr::encoding((array) $data, $to, $from);
    }

    /**
     * Get service config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return mixed|null
     */
    public function getConfig($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config->all();
        }

        if ($this->config->has($key)) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Get Base Uri.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * 生成应用证书SN.
     *
     * @author 大冰 https://sbing.vip/archives/2019-new-alipay-php-docking.html
     *
     * @param $certPath
     *
     * @throws /Exception
     */
    public static function getCertSN($certPath): string
    {
        if (!is_file($certPath)) {
            throw new Exception('unknown certPath -- [getCertSN]');
        }
        $x509data = file_get_contents($certPath);
        if (false === $x509data) {
            throw new Exception('Momoyu CertSN Error -- [getCertSN]');
        }
        openssl_x509_read($x509data);
        $certdata = openssl_x509_parse($x509data);
        if (empty($certdata)) {
            throw new Exception('Momoyu openssl_x509_parse Error -- [getCertSN]');
        }
        $issuer_arr = [];
        foreach ($certdata['issuer'] as $key => $val) {
            $issuer_arr[] = $key.'='.$val;
        }
        $issuer = implode(',', array_reverse($issuer_arr));
        Log::debug('getCertSN:', [$certPath, $issuer, $certdata['serialNumber']]);

        return md5($issuer.$certdata['serialNumber']);
    }

    /**
     * processingApiResult.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $data
     * @param $result
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    protected static function processingApiResult($data, $result, $response = false): Collection
    {
        if ($response) {
            return new Collection($result);
        }

        $method = str_replace('.', '_', $data['method']).'_response';

        if (!isset($result['sign']) || '10000' != $result[$method]['code']) {
            throw new GatewayException('Get Momoyu API Error:'.$result[$method]['msg'].(isset($result[$method]['sub_code']) ? (' - '.$result[$method]['sub_code']) : ''), $result);
        }

        if (self::verifySign($result[$method], true, $result['sign'])) {
            return new Collection($result[$method]);
        }

        Events::dispatch(new Events\SignFailed('Momoyu', '', $result));

        throw new InvalidSignException('Momoyu Sign Verify FAILED', $result);
    }

    /**
     * Set Http options.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function setHttpOptions(): self
    {
        if ($this->config->has('http') && is_array($this->config->get('http'))) {
            $this->config->forget('http.base_uri');
            $this->httpOptions = $this->config->get('http');
        }

        return $this;
    }
}
