<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\JsapiUnifiedOrderParams;
use Ipaynow\Response\JsapiUnifiedOrderResponse;
use Ipaynow\SpecRequest;

class JsapiUnifiedOrderRequest extends SpecRequest
{
    public function __construct(JsapiUnifiedOrderParams $params, $version = '1.0.0')
    {
        parent::__construct('WP001', $version, 'UTF-8', $params, JsapiUnifiedOrderResponse::class, 'mhtSignType');
    }
}
