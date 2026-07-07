<?php

namespace Ipaynow\Domain;

class AggregateCodeUnifiedOrderParams extends AbstractParams
{
    /** 支付场景 */
    public $deviceType = '20';
    /** 商户订单币种类型 */
    public $mhtCurrencyType = '156';
    /** 是否支持信用卡支付（0：不限制，1：禁用） */
    public $mhtLimitPay;
    /** 商户订单交易金额（单位：分），必填 */
    public $mhtOrderAmt;
    /** 商户订单详情 */
    public $mhtOrderDetail;
    /** 商户订单名称 */
    public $mhtOrderName;
    /** 商户订单号，必填 */
    public $mhtOrderNo;
    /** 商户订单开始时间（格式：yyyyMMddHHmmss） */
    public $mhtOrderStartTime;
    /** 商户订单超时时间（秒） */
    public $mhtOrderTimeOut = 120;
    /** 商户订单类型 05-代理消费 */
    public $mhtOrderType = '05';
    /** 商户保留域 */
    public $mhtReserved;
    /** 商户后台通知URL */
    public $notifyUrl;
    /** 输出类型（0：返回二维码串，1：返回支付链接，默认1） */
    public $outputType;
}
