<?php

namespace Ipaynow\Response;

use Ipaynow\IpaynowResponse;

class ReverseQueryResponse extends IpaynowResponse
{
    public function isSuccess()
    {
        return $this->get('responseCode') === 'R000';
    }
}
