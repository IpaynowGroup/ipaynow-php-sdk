<?php

namespace Ipaynow\Response;

use Ipaynow\IpaynowResponse;

class ReverseResponse extends IpaynowResponse
{
    public function isSuccess()
    {
        return $this->get('responseCode') === 'R000';
    }
}
