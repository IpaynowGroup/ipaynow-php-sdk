<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\RefundParams;
use Ipaynow\Response\RefundResponse;
use Ipaynow\SpecRequest;

class RefundRequest extends SpecRequest
{
    public function __construct(RefundParams $params, $version = '1.0.0')
    {
        parent::__construct(
            'R001',
            $version,
            'UTF-8',
            $params,
            RefundResponse::class,
            'signType',
            '/refund/refundOrder',
            true
        );
    }
}
