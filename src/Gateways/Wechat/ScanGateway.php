<?php

namespace liuyuit\Pay\Gateways\Wechat;

use Symfony\Component\HttpFoundation\Request;
use liuyuit\Pay\Events;
use liuyuit\Pay\Exceptions\GatewayException;
use liuyuit\Pay\Exceptions\InvalidArgumentException;
use liuyuit\Pay\Exceptions\InvalidSignException;
use Yansongda\Supports\Collection;

class ScanGateway extends Gateway
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
        $payload['spbill_create_ip'] = Request::createFromGlobals()->server->get('SERVER_ADDR');
        $payload['trade_type'] = $this->getTradeType();

        Events::dispatch(new Events\PayStarted('Wechat', 'Scan', $endpoint, $payload));

        return $this->preOrder($payload);
    }

    /**
     * Get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getTradeType(): string
    {
        return 'NATIVE';
    }
}
