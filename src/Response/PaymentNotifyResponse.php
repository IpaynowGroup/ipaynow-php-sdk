<?php

namespace Ipaynow\Response;

use Ipaynow\IpaynowResponse;

class PaymentNotifyResponse extends IpaynowResponse
{
    public function isSuccess()
    {
        return $this->get('transStatus') === 'A001';
    }
}
