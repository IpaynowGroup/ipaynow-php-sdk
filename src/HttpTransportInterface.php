<?php

namespace Ipaynow;

interface HttpTransportInterface
{
    /**
     * @param string $url
     * @param array<string,string> $params
     * @return string 原始响应报文（表单串）
     */
    public function post($url, array $params);
}
