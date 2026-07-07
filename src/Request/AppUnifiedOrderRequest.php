<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\AppUnifiedOrderParams;
use Ipaynow\Response\AppUnifiedOrderResponse;
use Ipaynow\SpecRequest;

class AppUnifiedOrderRequest extends SpecRequest
{
    public function __construct(AppUnifiedOrderParams $params, $version = '1.0.3')
    {
        parent::__construct('WP001', $version, 'UTF-8', $params, AppUnifiedOrderResponse::class, 'mhtSignType');
    }
}
