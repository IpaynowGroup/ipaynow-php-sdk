<?php

namespace Ipaynow\Domain;

class ReverseParams extends AbstractParams
{
    /** 撤销单号，必填 */
    public $mhtRefundNo;
    /** 商户订单号（需撤销的原订单号），必填 */
    public $mhtOrderNo;
    /** 重复支付时通过原交易单号指定要撤销的交易 */
    public $oriTransId;
    /** 撤销原因 */
    public $reason;
}
