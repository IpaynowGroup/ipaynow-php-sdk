<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\MiniProgramUnifiedOrderParams;
use Ipaynow\Response\MiniProgramUnifiedOrderResponse;
use Ipaynow\SpecRequest;

class MiniProgramUnifiedOrderRequest extends SpecRequest
{
    public function __construct(MiniProgramUnifiedOrderParams $params, $version = '1.0.0')
    {
        parent::__construct('WP001', $version, 'UTF-8', $params, MiniProgramUnifiedOrderResponse::class, 'mhtSignType');
    }
}
