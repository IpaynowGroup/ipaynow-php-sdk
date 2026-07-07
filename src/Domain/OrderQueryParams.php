<?php

namespace Ipaynow\Domain;

class OrderQueryParams extends AbstractParams
{
    /** 设备类型 */
    public $deviceType;
    /** 商户订单号（需查询的订单号），必填 */
    public $mhtOrderNo;
    /** 接口版本号，传入时覆盖请求 version（默认 "1.0.0"） */
    public $version;
}
