<?php

namespace liuyuit\XyPaySdk\Gateways;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use liuyuit\XyPaySdk\Contracts\GatewayApplicationInterface;
use liuyuit\XyPaySdk\Contracts\GatewayInterface;
use liuyuit\XyPaySdk\Events;
use liuyuit\XyPaySdk\Exceptions\GatewayException;
use liuyuit\XyPaySdk\Exceptions\InvalidArgumentException;
use liuyuit\XyPaySdk\Exceptions\InvalidConfigException;
use liuyuit\XyPaySdk\Exceptions\InvalidGatewayException;
use liuyuit\XyPaySdk\Exceptions\InvalidSignException;
use liuyuit\XyPaySdk\Gateways\Momoyu\Support;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

/**
 * @method Response app(array $config) APP 支付
 */
class Momoyu implements GatewayApplicationInterface
{
    /**
     * Const mode_normal.
     */
    const MODE_NORMAL = 'normal';

    /**
     * Const mode_dev.
     */
    const MODE_DEV = 'dev';

    /**
     * Const mode_service.
     */
    const MODE_SERVICE = 'service';

    /**
     * Const url.
     */
    const URL = [
        self::MODE_NORMAL => 'https://ohayoo.cn/game_sdk/light_game/trade/partner/order_status',
        self::MODE_SERVICE => 'https://ohayoo.cn/game_sdk/light_game/trade/partner/order_status',
        self::MODE_DEV => 'https://ohayoo.cn/game_sdk/light_game/trade/partner/order_status',
    ];

    /**
     * Momoyu payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Momoyu gateway.
     *
     * @var string
     */
    protected $gateway;

    /**
     * extends.
     *
     * @var array
     */
    protected $extends;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        $this->gateway = Support::create($config)->getBaseUri();
        $this->payload = [
            'notify_url' => $config->get('notify_url'),
        ];
    }

    /**
     * Magic pay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array  $params
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidGatewayException
     * @throws InvalidSignException
     *
     * @return Response|Collection
     */
    public function __call($method, $params)
    {
        if (isset($this->extends[$method])) {
            return $this->makeExtend($method, ...$params);
        }

        return $this->pay($method, ...$params);
    }

    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     * @param array  $params
     *
     * @throws InvalidGatewayException
     *
     * @return Response|Collection
     */
    public function pay($gateway, $params = [])
    {
        Events::dispatch(new Events\PayStarting('Momoyu', $gateway, $params));

        $this->payload['notify_url'] = $params['notify_url'] ?? $this->payload['notify_url'];

        $this->payload['open_id'] = $params['uid'];
        $this->payload['order_no'] = $params['out_trade_no'];
        $this->payload['product_id'] = $params['total_amount'];
        $this->payload['subject'] = $params['subject'];
        $this->payload['total_amount'] = $params['total_amount'];
        $this->payload['trade_time'] = time();
        $this->payload['valid_time'] = 60;

        $gateway = get_class($this).'\\'.Str::studly($gateway).'Gateway';

        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }

        throw new InvalidGatewayException("Pay Gateway [{$gateway}] not exists");
    }

    /**
     * Verify sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array|null $data
     *
     * @throws InvalidSignException
     * @throws InvalidConfigException
     */
    public function verify($data = null, bool $refund = false): Collection
    {
        if (is_null($data)) {
            $request = Request::createFromGlobals();

            $data = $request->request->count() > 0 ? $request->request->all() : $request->query->all();
        }

        Events::dispatch(new Events\RequestReceived('Momoyu', '', $data));

        if (Support::verifySign($data)) {
            return new Collection($data);
        }

        Events::dispatch(new Events\SignFailed('Momoyu', '', $data));

        throw new InvalidSignException('Alipay Sign Verify FAILED', $data);
    }

    /**
     * Query an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public function find($order, string $type = 'wap'): Collection
    {
    }

    /**
     * Refund an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public function refund(array $order): Collection
    {
    }

    /**
     * Cancel an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array|string $order
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public function cancel($order): Collection
    {
    }

    /**
     * Close an order.
     *
     * @param string|array $order
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public function close($order): Collection
    {
    }

    /**
     * Download bill.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $bill
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    public function download($bill): string
    {
    }

    /**
     * Reply success to alipay.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function success(): Response
    {
    }

    /**
     * extend.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws GatewayException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws InvalidArgumentException
     */
    public function extend(string $method, callable $function, bool $now = true, bool $response = false): ?Collection
    {
        if (!$now && !method_exists($this, $method)) {
            $this->extends[$method] = $function;

            return null;
        }

        $customize = $function($this->payload);

        if (!is_array($customize) && !($customize instanceof Collection)) {
            throw new InvalidArgumentException('Return Type Must Be Array Or Collection');
        }

        Events::dispatch(new Events\MethodCalled('Momoyu', 'extend', $this->gateway, $customize));

        if (is_array($customize)) {
            $this->payload = $customize;
            $this->payload['sign'] = Support::generateSign($this->payload);

            return Support::requestApi($this->payload, $response);
        }

        return $customize;
    }

    /**
     * Make pay gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws InvalidGatewayException
     *
     * @return Response|Collection
     */
    protected function makePay(string $gateway)
    {
        $app = new $gateway();

        if ($app instanceof GatewayInterface) {
            return $app->pay($this->gateway, array_filter($this->payload, function ($value) {
                return '' !== $value && !is_null($value);
            }));
        }

        throw new InvalidGatewayException("Pay Gateway [{$gateway}] Must Be An Instance Of GatewayInterface");
    }

    /**
     * makeExtend.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     */
    protected function makeExtend(string $method, array ...$params): Collection
    {
        $params = count($params) >= 1 ? $params[0] : $params;

        $function = $this->extends[$method];

        $customize = $function($this->payload, $params);

        if (!is_array($customize) && !($customize instanceof Collection)) {
            throw new InvalidArgumentException('Return Type Must Be Array Or Collection');
        }

        Events::dispatch(new Events\MethodCalled(
            'Momoyu',
            'extend - '.$method,
            $this->gateway,
            is_array($customize) ? $customize : $customize->toArray()
        ));

        if (is_array($customize)) {
            $this->payload = $customize;
            $this->payload['sign'] = Support::generateSign($this->payload);

            return Support::requestApi($this->payload);
        }

        return $customize;
    }
}
