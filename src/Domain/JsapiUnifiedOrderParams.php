<?php

namespace Ipaynow\Domain;

class JsapiUnifiedOrderParams extends AbstractParams
{
    /** 客户端IP，选填 */
    public $consumerCreateIp;
    /** 消费者ID（outputType=1必填，渠道类型为11时不填） */
    public $consumerId;
    /** 设备类型，固定值"0600"（公众号） */
    public $deviceType = '0600';
    /** 商户前台通知URL（outputType=0时必填） */
    public $frontNotifyUrl;
    /** 商户订单币种类型，固定值"156"（人民币） */
    public $mhtCurrencyType = '156';
    /** 商品标记（用于营销活动） */
    public $mhtGoodsTag;
    /** 单品营销 JSON 串 */
    public $mhtGoodsDetails;
    /** 是否支持信用卡支付（0=不限制卡类型，1=不能使用信用卡） */
    public $mhtLimitPay;
    /** 商户订单交易金额（单位：分），必填 */
    public $mhtOrderAmt;
    /** 商户订单详情 */
    public $mhtOrderDetail;
    /** 商户商品名称 */
    public $mhtOrderName;
    /** 商户订单号（必须唯一），必填 */
    public $mhtOrderNo;
    /** 商户订单开始时间（格式：yyyyMMddHHmmss） */
    public $mhtOrderStartTime;
    /** 商户订单超时时间（秒） */
    public $mhtOrderTimeOut = 120;
    /** 商户交易类型，固定值"05"（代理消费） */
    public $mhtOrderType = '05';
    /** 商户保留域 */
    public $mhtReserved;
    /** 商户appId（outputType=1且payChannelType=13/11时必传） */
    public $mhtSubAppId;
    /** 商户后台通知URL */
    public $notifyUrl;
    /** 输出格式（0=直接调起支付，1=返回支付凭证） */
    public $outputType;
    /** 用户所选渠道类型（12=支付宝，13=微信，11=银联） */
    public $payChannelType;
}
