<?php

namespace Ipaynow\Domain;

class H5UnifiedOrderParams extends AbstractParams
{
    /** 用户支付ip，微信渠道特定outputType时必填 */
    public $consumerCreateIp;
    /** 消费者id，招行一网通渠道必填 */
    public $consumerId;
    /** 设备类型，固定值 */
    public $deviceType = '0601';
    /** 商户前台通知URL，HTTPS协议 */
    public $frontNotifyUrl;
    /** 商户订单币种类型，固定值 */
    public $mhtCurrencyType = '156';
    /** 是否支持信用卡支付 */
    public $mhtLimitPay;
    /** 商户订单交易金额，单位分，必填 */
    public $mhtOrderAmt;
    /** 商户订单详情 */
    public $mhtOrderDetail;
    /** 商户商品名称 */
    public $mhtOrderName;
    /** 商户订单号，必填 */
    public $mhtOrderNo;
    /** 商户订单开始时间，格式yyyyMMddHHmmss */
    public $mhtOrderStartTime;
    /** 商户订单超时时间（秒） */
    public $mhtOrderTimeOut = 120;
    /** 商户交易类型，固定值05 代理消费 */
    public $mhtOrderType = '05';
    /** 商户保留域 */
    public $mhtReserved;
    /** 商户后台通知URL，HTTPS协议 */
    public $notifyUrl;
    /** 输出格式 */
    public $outputType;
    /** 用户所选渠道类型，银联:20，支付宝:12，微信:13等 */
    public $payChannelType;
    public $specifiedChannel;
    public $payAccNo;
    public $guideId;
}
