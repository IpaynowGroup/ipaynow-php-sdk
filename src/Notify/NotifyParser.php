<?php

namespace Ipaynow\Notify;

use Ipaynow\IpaynowErrorCode;
use Ipaynow\IpaynowException;
use Ipaynow\IpaynowConfig;
use Ipaynow\Parser;
use Ipaynow\Response\PaymentNotifyResponse;
use Ipaynow\Response\RefundNotifyResponse;
use Ipaynow\Signer;

final class NotifyParser
{
    private function __construct()
    {
    }

    /**
     * N001 支付结果通知，属于正向交易通知，signType 参与签名。
     */
    public static function parsePayment($body, IpaynowConfig $config)
    {
        return self::parse($body, $config, false, new PaymentNotifyResponse());
    }

    /**
     * RN001 退款结果通知，属于退款链路通知，signType 不参与签名。
     */
    public static function parseRefund($body, IpaynowConfig $config)
    {
        return self::parse($body, $config, true, new RefundNotifyResponse());
    }

    private static function parse($body, IpaynowConfig $config, $excludeSignType, $response)
    {
        $fields = Parser::parseResponse($body);
        if (!Signer::verify($fields, $config, 'signature', $excludeSignType)) {
            throw new IpaynowException(IpaynowErrorCode::SIGN_ERROR, '现在支付回调通知验签失败');
        }
        $response->load($fields, $body);
        return $response;
    }
}
