<?php

namespace Ipaynow;

final class Parser
{
    private function __construct()
    {
    }

    /**
     * 解析 k=v&k2=v2 形式的表单响应串，值做 URL 解码。
     *
     * @param string|null $text
     * @return array<string,string>
     */
    public static function parseResponse($text)
    {
        $trimmed = trim((string) $text);
        if ($trimmed === '') {
            return array();
        }
        $out = array();
        foreach (explode('&', $trimmed) as $part) {
            $pieces = explode('=', $part, 2);
            $key = urldecode($pieces[0]);
            $value = isset($pieces[1]) ? urldecode($pieces[1]) : '';
            $out[$key] = $value;
        }
        return $out;
    }
}
