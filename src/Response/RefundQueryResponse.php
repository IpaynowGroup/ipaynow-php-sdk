<?php

namespace Ipaynow\Response;

use Ipaynow\IpaynowResponse;

class RefundQueryResponse extends IpaynowResponse
{
    public function isSuccess()
    {
        return $this->get('responseCode') === 'R000';
    }
}
