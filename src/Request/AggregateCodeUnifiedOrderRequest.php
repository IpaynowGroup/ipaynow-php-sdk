<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\AggregateCodeUnifiedOrderParams;
use Ipaynow\Response\AggregateCodeUnifiedOrderResponse;
use Ipaynow\SpecRequest;

class AggregateCodeUnifiedOrderRequest extends SpecRequest
{
    public function __construct(AggregateCodeUnifiedOrderParams $params, $version = '1.0.3')
    {
        parent::__construct('WP001', $version, 'UTF-8', $params, AggregateCodeUnifiedOrderResponse::class, 'mhtSignType');
    }
}
