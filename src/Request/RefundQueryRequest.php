<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\RefundQueryParams;
use Ipaynow\Response\RefundQueryResponse;
use Ipaynow\SpecRequest;

class RefundQueryRequest extends SpecRequest
{
    public function __construct(RefundQueryParams $params, $version = '1.0.0')
    {
        parent::__construct(
            'Q001',
            $version,
            'UTF-8',
            $params,
            RefundQueryResponse::class,
            'signType',
            '/refund/refundQuery',
            true
        );
    }
}
