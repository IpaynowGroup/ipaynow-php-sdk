<?php

namespace Ipaynow\Domain;

class MiniProgramUnifiedOrderParams extends AbstractParams
{
    /** 消费者ID（outputType=1必填，微信openid或支付宝buyer_id） */
    public $consumerId;
    /** 设备类型（14：小程序支付，24：小程序收银台） */
    public $deviceType = '14';
    /** 订单优惠金额（单位：分） */
    public $discountAmt;
    /** 商户订单币种类型 */
    public $mhtCurrencyType = '156';
    /** 商品标记（用于营销活动） */
    public $mhtGoodsTag;
    /** 优惠 JSON 串 */
    public $mhtGoodsDetails;
    /** 是否支持信用卡支付（0：不支持，1：允许） */
    public $mhtLimitPay;
    /** 用户实付金额（单位：分），必填 */
    public $mhtOrderAmt;
    /** 商户订单详情 */
    public $mhtOrderDetail;
    /** 商户商品名称 */
    public $mhtOrderName;
    /** 商户订单号，必填 */
    public $mhtOrderNo;
    /** 商户订单开始时间（格式：yyyyMMddHHmmss） */
    public $mhtOrderStartTime;
    /** 商户订单超时时间（秒） */
    public $mhtOrderTimeOut = 120;
    /** 商户交易类型（05：代理消费） */
    public $mhtOrderType = '05';
    /** 商户保留域 */
    public $mhtReserved;
    /** 商户appId（deviceType=14且payChannelType=13/11时必传） */
    public $mhtSubAppId;
    /** 商户后台通知URL */
    public $notifyUrl;
    /** 订单原始金额（单位：分） */
    public $oriMhtOrderAmt;
    /** 输出格式（固定值1） */
    public $outputType = '1';
    /** 用户所选渠道类型（13：微信，12：支付宝，11：银联） */
    public $payChannelType;
    public $mhtStoreId;
}
