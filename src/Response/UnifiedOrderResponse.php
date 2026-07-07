<?php

namespace Ipaynow\Response;

use Ipaynow\IpaynowResponse;

class UnifiedOrderResponse extends IpaynowResponse
{
    /**
     * 返回下单响应里的支付要素。兼容大写 TN。
     */
    public function getTn()
    {
        $tn = $this->get('tn');
        return $tn !== null ? $tn : $this->get('TN');
    }

    /**
     * 返回现在支付流水号。
     */
    public function getNowPayOrderNo()
    {
        return $this->get('nowPayOrderNo');
    }
}
