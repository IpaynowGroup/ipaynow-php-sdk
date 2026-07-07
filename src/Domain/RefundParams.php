<?php

namespace Ipaynow\Domain;

class RefundParams extends AbstractParams
{
    /** 退款金额（单位：分，必须小于等于原订单金额），必填 */
    public $amount;
    /** 商户订单号（需退款的原订单号），必填 */
    public $mhtOrderNo;
    /** 商户退款单号（必须唯一），必填 */
    public $mhtRefundNo;
    /** 商户退款结果通知URL */
    public $notifyUrl;
    /** 重复支付时通过原交易单号指定要退款的交易 */
    public $oriTransId;
    /** 退款原因 */
    public $reason;
}
