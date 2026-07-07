<?php

namespace Ipaynow\Domain;

class AppUnifiedOrderParams extends AbstractParams
{
    /** 消费者下单ip，微信支付时必填 */
    public $consumerCreateIp;
    /** 消费者ID，payChannelType=17时必传 */
    public $consumerId;
    /** 消费者名称 */
    public $consumerName;
    /** 设备类型，定值01 */
    public $deviceType = '01';
    /** 商户订单币种类型，156人民币 */
    public $mhtCurrencyType = '156';
    /** 是否支持信用卡支付，0禁用，1允许 */
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
    /** 商户交易类型，05代理消费 */
    public $mhtOrderType = '05';
    /** 商户保留域，格式mchBankId=123&商户自定义参数 */
    public $mhtReserved;
    /** 微信开放平台appId */
    public $mhtSubAppId;
    /** 商户后台通知URL */
    public $notifyUrl;
    /** 用户所选渠道类型 */
    public $payChannelType;
    /** 输出格式 */
    public $outputType = '0';
    public $frontNotifyUrl;
    public $mhtStoreId;
}
