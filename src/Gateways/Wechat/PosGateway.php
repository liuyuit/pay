<?php

namespace liuyuit\XyPaySdk\Gateways\Wechat;

use liuyuit\XyPaySdk\Events;
use liuyuit\XyPaySdk\Exceptions\GatewayException;
use liuyuit\XyPaySdk\Exceptions\InvalidArgumentException;
use liuyuit\XyPaySdk\Exceptions\InvalidSignException;
use Yansongda\Supports\Collection;

class PosGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @throws GatewayException
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     */
    public function pay($endpoint, array $payload): Collection
    {
        unset($payload['trade_type'], $payload['notify_url']);

        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Wechat', 'Pos', $endpoint, $payload));

        return Support::requestApi('pay/micropay', $payload);
    }

    /**
     * Get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getTradeType(): string
    {
        return 'MICROPAY';
    }
}
