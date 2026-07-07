<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\ReverseParams;
use Ipaynow\Response\ReverseResponse;
use Ipaynow\SpecRequest;

class ReverseRequest extends SpecRequest
{
    public function __construct(ReverseParams $params, $version = '1.0.0')
    {
        parent::__construct(
            'R002',
            $version,
            'UTF-8',
            $params,
            ReverseResponse::class,
            'signType',
            '/refund/cancelOrder',
            true
        );
    }
}
