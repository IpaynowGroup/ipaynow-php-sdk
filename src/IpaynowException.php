<?php

namespace Ipaynow;

/**
 * 现在支付 SDK 统一异常，覆盖请求传输失败、响应或回调验签失败、响应类定义错误等。
 * 调用方只需捕获这一种异常，再通过 getErrorCode() 判断具体失败类型。
 */
class IpaynowException extends \RuntimeException
{
    /** @var string */
    private $errorCode;

    public function __construct($errorCode, $message, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
