<?php

namespace Ipaynow\Response;

use Ipaynow\IpaynowResponse;

class OrderQueryResponse extends IpaynowResponse
{
    public function getTransStatus()
    {
        return $this->get('transStatus');
    }

    public function getNowPayOrderNo()
    {
        return $this->get('nowPayOrderNo');
    }

    public function getPayTime()
    {
        return $this->get('payTime');
    }

    public function getChannelOrderNo()
    {
        return $this->get('channelOrderNo');
    }
}
