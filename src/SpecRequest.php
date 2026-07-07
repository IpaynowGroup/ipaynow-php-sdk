<?php

namespace Ipaynow;

use Ipaynow\Domain\AbstractParams;

/**
 * 单个操作的请求元数据：funcode/version/path/签名字段名/excludeSignType。
 * 每个操作在 Request\ 命名空间下有一个具体子类，构造时把这些常量喂给这里。
 */
abstract class SpecRequest
{
    /** @var string */
    private $funcode;
    /** @var string */
    private $version;
    /** @var string */
    private $mhtCharset;
    /** @var AbstractParams */
    private $params;
    /** @var string */
    private $responseClass;
    /** @var string */
    private $signTypeField;
    /** @var string */
    private $path;
    /** @var bool */
    private $excludeSignType;

    protected function __construct(
        $funcode,
        $version,
        $mhtCharset,
        AbstractParams $params,
        $responseClass,
        $signTypeField,
        $path = '/',
        $excludeSignType = false
    ) {
        $this->funcode = $funcode;
        $this->version = $version;
        $this->mhtCharset = $mhtCharset;
        $this->params = $params;
        $this->responseClass = $responseClass;
        $this->signTypeField = $signTypeField;
        $this->path = $path;
        $this->excludeSignType = $excludeSignType;
    }

    public function funcode()
    {
        return $this->funcode;
    }

    public function version()
    {
        return $this->version;
    }

    public function mhtCharset()
    {
        return $this->mhtCharset;
    }

    /**
     * @return array<string,string>
     */
    public function params()
    {
        return $this->params->toMap();
    }

    public function responseClass()
    {
        return $this->responseClass;
    }

    public function signTypeField()
    {
        return $this->signTypeField;
    }

    public function signatureField()
    {
        return 'mhtSignature';
    }

    public function responseSignatureField()
    {
        return 'signature';
    }

    public function path()
    {
        return $this->path;
    }

    public function excludeSignType()
    {
        return $this->excludeSignType;
    }
}
