<?php

namespace Ipaynow\Response;

use Ipaynow\IpaynowResponse;

class PaymentNotifyResponse extends IpaynowResponse
{
    public function isSuccess()
    {
        return $this->status() === 'A001';
    }

    /**
     * @return string|null transStatus 字段
     */
    public function getTransStatus()
    {
        return $this->get('transStatus');
    }

    /**
     * @return string|null tradeStatus 字段
     */
    public function getTradeStatus()
    {
        return $this->get('tradeStatus');
    }

    /**
     * 交易状态：优先取 transStatus，缺失时回退 tradeStatus。
     *
     * @return string|null
     */
    public function status()
    {
        $transStatus = $this->get('transStatus');
        return $transStatus !== null && $transStatus !== '' ? $transStatus : $this->get('tradeStatus');
    }
}
