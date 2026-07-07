<?php

namespace Ipaynow;

class IpaynowResponse
{
    /** @var array<string,string> */
    protected $fields = array();
    /** @var string|null */
    protected $rawBody;

    /**
     * @param array<string,string> $fields
     * @param string|null $rawBody
     */
    public function load(array $fields, $rawBody = null)
    {
        $this->fields = $fields;
        $this->rawBody = $rawBody;
    }

    public function get($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }

    /**
     * @return array<string,string>
     */
    public function fields()
    {
        return $this->fields;
    }

    public function code()
    {
        return $this->get('responseCode');
    }

    public function message()
    {
        return $this->get('responseMsg');
    }

    public function rawBody()
    {
        return $this->rawBody;
    }

    public function success()
    {
        return $this->isSuccess();
    }

    /**
     * 根据现在支付同步响应的 responseCode 判断接口调用是否成功。
     * 回调通知使用不同字段，由子类覆盖此方法，见
     * Response\PaymentNotifyResponse 和 Response\RefundNotifyResponse。
     */
    public function isSuccess()
    {
        return $this->get('responseCode') === 'A001';
    }
}
