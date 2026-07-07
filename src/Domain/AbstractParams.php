<?php

namespace Ipaynow\Domain;

/**
 * 每操作 Params 的公共基类：以关联数组构造（未显式设置的字段使用类属性默认值），
 * 支持 extraParams 透传任意附加字段。
 *
 * 与 Java BeanMap 行为一致：extraParams 在命名字段之后合并进结果，
 * 但不能覆盖 SDK 保留字段（funcode/version/appId/mhtCharset/mhtSignType/
 * signType/mhtSignature/signature）。
 */
abstract class AbstractParams
{
    /** @var array<string,string> */
    protected $extraParams = array();

    private static $reservedFields = array(
        'funcode', 'version', 'appId', 'mhtCharset', 'mhtSignType', 'signType', 'mhtSignature', 'signature',
    );

    /**
     * @param array<string,mixed> $data 字段名 => 值；可包含 'extraParams' => array
     */
    public function __construct(array $data = array())
    {
        if (isset($data['extraParams']) && is_array($data['extraParams'])) {
            $this->extraParams = $data['extraParams'];
            unset($data['extraParams']);
        }
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return array<string,string>
     */
    public function toMap()
    {
        $out = array();
        foreach (get_object_vars($this) as $key => $value) {
            if ($key === 'extraParams' || $value === null) {
                continue;
            }
            $out[$key] = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        }
        foreach ($this->extraParams as $key => $value) {
            if (in_array($key, self::$reservedFields, true)) {
                throw new \InvalidArgumentException('extraParams 不能覆盖 SDK 保留字段: ' . $key);
            }
            if ($value !== null) {
                $out[$key] = (string) $value;
            }
        }
        return $out;
    }
}
