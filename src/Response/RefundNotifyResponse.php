<?php

namespace Ipaynow\Response;

use Ipaynow\IpaynowResponse;

class RefundNotifyResponse extends IpaynowResponse
{
    public function isSuccess()
    {
        return $this->get('tradeStatus') === 'A001';
    }
}
