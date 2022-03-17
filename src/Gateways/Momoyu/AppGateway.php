<?php

namespace liuyuit\Pay\Gateways\Momoyu;

use Symfony\Component\HttpFoundation\Response;
use liuyuit\Pay\Events;
use liuyuit\Pay\Exceptions\InvalidArgumentException;
use liuyuit\Pay\Exceptions\InvalidConfigException;

class AppGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     */
    public function pay($endpoint, array $payload): Response
    {
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Momoyu', 'App', $endpoint, $payload));

        return new Response(json_encode($payload));
    }
}
