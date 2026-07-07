<?php

namespace Ipaynow\Domain;

class ReverseQueryParams extends AbstractParams
{
    /** 商户订单号（需查询的原订单号），必填 */
    public $mhtRefundNo;
}
