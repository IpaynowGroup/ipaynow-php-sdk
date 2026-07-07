<?php

namespace Ipaynow\Domain;

class RefundQueryParams extends AbstractParams
{
    /** 商户退款单号（需查询的退款单号），必填 */
    public $mhtRefundNo;
}
