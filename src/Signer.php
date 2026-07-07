<?php

namespace Ipaynow;

/**
 * 正向交易（WP001/MQ002/N001）的 signType 参与签名。
 * 退款和撤销链路（R001/Q001/R002/Q002/RN001）的 signType 不参与签名。
 * mhtSignType 是正向交易请求里的签名类型字段，字段名不同，永远参与签名。
 */
final class Signer
{
    private function __construct()
    {
    }

    /**
     * @param array<string,string> $params
     * @param IpaynowConfig $config
     * @param bool $excludeSignType
     * @param array<int,string> $signatureFields 额外要从签名串中剔除的字段（如签名字段自身）
     * @return string
     */
    public static function sign(array $params, IpaynowConfig $config, $excludeSignType, array $signatureFields = array())
    {
        $canonical = self::canonical($params, $excludeSignType, $signatureFields);
        if ($config->signType() === SignType::RSA) {
            $signature = '';
            if (!openssl_sign($canonical, $signature, $config->mchPrivateKeyObject(), OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException('RSA 签名失败');
            }
            return base64_encode($signature);
        }
        return md5($canonical . '&' . md5($config->appKey()));
    }

    public static function verify(array $params, IpaynowConfig $config, $signatureField, $excludeSignType)
    {
        $actual = isset($params[$signatureField]) ? $params[$signatureField] : null;
        if ($actual === null || $actual === '') {
            return false;
        }
        if (isset($params['signType']) && $params['signType'] !== ''
            && strcasecmp($params['signType'], $config->signType()) !== 0) {
            return false;
        }
        $canonical = self::canonical($params, $excludeSignType, array($signatureField, 'mhtSignature'));
        if ($config->signType() === SignType::RSA) {
            $decoded = base64_decode($actual, true);
            return $decoded !== false
                && openssl_verify($canonical, $decoded, $config->ipaynowPublicKeyObject(), OPENSSL_ALGO_SHA256) === 1;
        }
        $expected = self::sign($params, $config, $excludeSignType, array($signatureField, 'mhtSignature'));
        return hash_equals($expected, strtolower($actual));
    }

    /**
     * @param array<string,string> $params
     * @param bool $excludeSignType
     * @param array<int,string> $signatureFields
     * @return string
     */
    private static function canonical(array $params, $excludeSignType, array $signatureFields)
    {
        $cleaned = self::clean($params);
        if ($excludeSignType) {
            unset($cleaned['signType']);
        }
        unset($cleaned['mhtSignature']);
        unset($cleaned['signature']);
        foreach ($signatureFields as $field) {
            unset($cleaned[$field]);
        }
        ksort($cleaned, SORT_STRING);
        $pairs = array();
        foreach ($cleaned as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }
        return implode('&', $pairs);
    }

    /**
     * @param array<string,string> $params
     * @return array<string,string>
     */
    public static function clean(array $params)
    {
        $out = array();
        foreach ($params as $key => $value) {
            if ($value !== null && $value !== '') {
                $out[$key] = $value;
            }
        }
        return $out;
    }
}
