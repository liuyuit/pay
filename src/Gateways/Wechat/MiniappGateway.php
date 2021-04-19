<?php

namespace liuyuit\Pay\Gateways\Wechat;

use liuyuit\Pay\Exceptions\GatewayException;
use liuyuit\Pay\Exceptions\InvalidArgumentException;
use liuyuit\Pay\Exceptions\InvalidSignException;
use liuyuit\Pay\Gateways\Wechat;
use Yansongda\Supports\Collection;

class MiniappGateway extends MpGateway
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
        $payload['appid'] = Support::getInstance()->miniapp_id;

        if (Wechat::MODE_SERVICE === $this->mode) {
            $payload['sub_appid'] = Support::getInstance()->sub_miniapp_id;
            $this->payRequestUseSubAppId = true;
        }

        return parent::pay($endpoint, $payload);
    }
}
