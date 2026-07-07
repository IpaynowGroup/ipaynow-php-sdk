<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\H5UnifiedOrderParams;
use Ipaynow\Response\H5UnifiedOrderResponse;
use Ipaynow\SpecRequest;

class H5UnifiedOrderRequest extends SpecRequest
{
    public function __construct(H5UnifiedOrderParams $params, $version = '1.0.0')
    {
        parent::__construct('WP001', $version, 'UTF-8', $params, H5UnifiedOrderResponse::class, 'mhtSignType');
    }
}
