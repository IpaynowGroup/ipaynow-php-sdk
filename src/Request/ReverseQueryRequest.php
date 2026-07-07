<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\ReverseQueryParams;
use Ipaynow\Response\ReverseQueryResponse;
use Ipaynow\SpecRequest;

class ReverseQueryRequest extends SpecRequest
{
    public function __construct(ReverseQueryParams $params, $version = '1.0.0')
    {
        parent::__construct(
            'Q002',
            $version,
            'UTF-8',
            $params,
            ReverseQueryResponse::class,
            'signType',
            '/refund/refundQuery',
            true
        );
    }
}
