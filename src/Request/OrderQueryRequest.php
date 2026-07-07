<?php

namespace Ipaynow\Request;

use Ipaynow\Domain\OrderQueryParams;
use Ipaynow\Response\OrderQueryResponse;
use Ipaynow\SpecRequest;

class OrderQueryRequest extends SpecRequest
{
    public function __construct(OrderQueryParams $params)
    {
        parent::__construct('MQ002', self::resolveVersion($params), 'UTF-8', $params, OrderQueryResponse::class, 'mhtSignType');
    }

    private static function resolveVersion(OrderQueryParams $params)
    {
        $fields = $params->toMap();
        return isset($fields['version']) ? $fields['version'] : '1.0.0';
    }
}
