<?php

namespace Ipaynow;

final class IpaynowConfig
{
    /** @var string */
    private $appId;
    /** @var string|null */
    private $appKey;
    /** @var string|null */
    private $mchPrivateKey;
    /** @var string|null */
    private $ipaynowPublicKey;
    /** @var mixed */
    private $mchPrivateKeyObject;
    /** @var mixed */
    private $ipaynowPublicKeyObject;
    /** @var string */
    private $signType;
    /** @var string */
    private $baseUrl;
    /** @var int */
    private $connectTimeoutMillis;
    /** @var int */
    private $readTimeoutMillis;

    /**
     * 不对参数做类型声明，以便对 null/非字符串输入做出显式校验（而不是让 PHP
     * 的标量类型检查在构造前就抛出 TypeError）。
     *
     * @param mixed $appId
     * @param mixed $appKey
     * @param mixed $baseUrl
     * @param mixed $connectTimeoutMillis
     * @param mixed $readTimeoutMillis
     */
    public function __construct(
        $appId,
        $appKey = null,
        $mchPrivateKey = null,
        $ipaynowPublicKey = null,
        $signType = SignType::MD5,
        $baseUrl = 'https://pay.ipaynow.cn',
        $connectTimeoutMillis = 5000,
        $readTimeoutMillis = 15000
    ) {
        $this->appId = self::requireNonBlank($appId, 'appId');
        $this->signType = $signType ?: SignType::MD5;
        if ($this->signType === SignType::MD5) {
            $this->appKey = self::requireNonBlank($appKey, 'appKey');
            $this->mchPrivateKey = null;
            $this->ipaynowPublicKey = null;
            $this->mchPrivateKeyObject = null;
            $this->ipaynowPublicKeyObject = null;
        } elseif ($this->signType === SignType::RSA) {
            $this->appKey = null;
            $this->mchPrivateKey = self::requireNonBlank($mchPrivateKey, 'mchPrivateKey');
            $this->ipaynowPublicKey = self::requireNonBlank($ipaynowPublicKey, 'ipaynowPublicKey');
            $this->mchPrivateKeyObject = self::parsePrivateKey($this->mchPrivateKey);
            $this->ipaynowPublicKeyObject = self::parsePublicKey($this->ipaynowPublicKey);
        } else {
            throw new \InvalidArgumentException('不支持的签名类型: ' . $this->signType);
        }
        $this->baseUrl = self::requireNonBlank($baseUrl, 'baseUrl');
        if (!is_numeric($connectTimeoutMillis) || !is_numeric($readTimeoutMillis)
            || $connectTimeoutMillis <= 0 || $readTimeoutMillis <= 0) {
            throw new \InvalidArgumentException(
                '超时时间必须大于 0：connectTimeoutMillis=' . $connectTimeoutMillis
                . '，readTimeoutMillis=' . $readTimeoutMillis
            );
        }
        $this->connectTimeoutMillis = (int) $connectTimeoutMillis;
        $this->readTimeoutMillis = (int) $readTimeoutMillis;
    }

    private static function requireNonBlank($value, $name)
    {
        if (!is_string($value) || trim($value) === '') {
            throw new \InvalidArgumentException($name . ' 不能为空');
        }
        return $value;
    }

    public function appId()
    {
        return $this->appId;
    }

    public function appKey()
    {
        return $this->appKey;
    }

    public function mchPrivateKey()
    {
        return $this->mchPrivateKey;
    }

    public function ipaynowPublicKey()
    {
        return $this->ipaynowPublicKey;
    }

    public function mchPrivateKeyObject()
    {
        return $this->mchPrivateKeyObject;
    }

    public function ipaynowPublicKeyObject()
    {
        return $this->ipaynowPublicKeyObject;
    }

    public function signType()
    {
        return $this->signType;
    }

    public function baseUrl()
    {
        return $this->baseUrl;
    }

    public function connectTimeoutMillis()
    {
        return $this->connectTimeoutMillis;
    }

    public function readTimeoutMillis()
    {
        return $this->readTimeoutMillis;
    }

    public function endpoint($path)
    {
        $normalizedPath = ($path === null || $path === '') ? '/' : $path;
        if ($normalizedPath[0] !== '/') {
            $normalizedPath = '/' . $normalizedPath;
        }
        return rtrim($this->baseUrl, '/') . $normalizedPath;
    }

    private static function parsePrivateKey($base64)
    {
        $key = openssl_pkey_get_private(self::wrapPem(self::decodeBase64($base64, 'mchPrivateKey'), 'PRIVATE KEY'));
        if ($key === false) {
            throw new \InvalidArgumentException('mchPrivateKey 非法，需传裸 base64 PKCS8 私钥');
        }
        return $key;
    }

    private static function parsePublicKey($base64)
    {
        $key = openssl_pkey_get_public(self::wrapPem(self::decodeBase64($base64, 'ipaynowPublicKey'), 'PUBLIC KEY'));
        if ($key === false) {
            throw new \InvalidArgumentException('ipaynowPublicKey 非法，需传裸 base64 X509 公钥');
        }
        return $key;
    }

    private static function decodeBase64($value, $name)
    {
        if (strpos($value, '-----BEGIN') !== false || strpos($value, '-----END') !== false) {
            throw new \InvalidArgumentException($name . ' 仅支持裸 base64，不支持 PEM');
        }
        $decoded = base64_decode(preg_replace('/\s+/', '', $value), true);
        if ($decoded === false) {
            throw new \InvalidArgumentException($name . ' 非法，需传裸 base64');
        }
        return $decoded;
    }

    private static function wrapPem($binary, $label)
    {
        return "-----BEGIN " . $label . "-----\n"
            . chunk_split(base64_encode($binary), 64, "\n")
            . "-----END " . $label . "-----\n";
    }
}
