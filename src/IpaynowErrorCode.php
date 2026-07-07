<?php

namespace Ipaynow;

/**
 * SDK 统一错误码。调用方通过 IpaynowException::getErrorCode() 分支处理，
 * 不需要解析异常消息。这里仅定义 SDK 实际会抛出的失败码；业务成功/失败
 * 从 IpaynowResponse::isSuccess() 读取。
 */
final class IpaynowErrorCode
{
    /** 响应类实例化失败等 SDK 内部错误 */
    const SYSTEM_ERROR = 'E0001';
    /** HTTP 请求异常，包含连接失败、非 2xx 响应等 */
    const HTTP_EXCEPTION = 'E0002';
    /** 建立连接时超时 */
    const CONNECT_TIMEOUT = 'E0003';
    /** 等待或读取响应时超时 */
    const SOCKET_TIMEOUT = 'E0004';
    /** 同步响应或回调通知签名缺失或验签失败 */
    const SIGN_ERROR = 'E0012';

    private static $messages = array(
        self::SYSTEM_ERROR => '组件内部异常',
        self::HTTP_EXCEPTION => 'HTTP异常',
        self::CONNECT_TIMEOUT => 'ConnectTimeOut',
        self::SOCKET_TIMEOUT => 'SocketTimeOut',
        self::SIGN_ERROR => '渠道报文验签失败',
    );

    private function __construct()
    {
    }

    public static function defaultMessage($code)
    {
        return isset(self::$messages[$code]) ? self::$messages[$code] : '';
    }
}
