<?php

namespace Ipaynow;

/**
 * 默认的 HTTP 传输实现，基于 PHP cURL 扩展，表单编码 POST。
 *
 * 超时分类说明：cURL 对建连超时和读超时共用同一个错误码
 * CURLE_OPERATION_TIMEDOUT（28），无法像部分语言 HTTP 客户端那样精确区分。
 * 这里用启发式判断：取 CURLINFO_CONNECT_TIME，如果 TCP/TLS 连接尚未完成
 * （耗时为 0），判定为建连超时（E0003）；否则判定为读超时（E0004）。
 * 该启发式在网络延迟极小的场景可能有误判风险，但目前是 cURL 能提供的
 * 最接近的信号。
 */
final class CurlTransport implements HttpTransportInterface
{
    /** @var int */
    private $connectTimeoutMillis;
    /** @var int */
    private $readTimeoutMillis;

    public function __construct($connectTimeoutMillis = 5000, $readTimeoutMillis = 15000)
    {
        $this->connectTimeoutMillis = $connectTimeoutMillis;
        $this->readTimeoutMillis = $readTimeoutMillis;
    }

    public function post($url, array $params)
    {
        $funcode = isset($params['funcode']) ? $params['funcode'] : '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeoutMillis);
        // CURLOPT_TIMEOUT_MS 是整个请求（含建连+读取）的总超时上限。
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->connectTimeoutMillis + $this->readTimeoutMillis);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $connectTime = (float) curl_getinfo($ch, CURLINFO_CONNECT_TIME);
        curl_close($ch);

        if ($errno === CURLE_OPERATION_TIMEDOUT) {
            if ($connectTime <= 0) {
                throw new IpaynowException(
                    IpaynowErrorCode::CONNECT_TIMEOUT,
                    IpaynowErrorCode::defaultMessage(IpaynowErrorCode::CONNECT_TIMEOUT) . '，功能码=' . $funcode
                );
            }
            throw new IpaynowException(
                IpaynowErrorCode::SOCKET_TIMEOUT,
                IpaynowErrorCode::defaultMessage(IpaynowErrorCode::SOCKET_TIMEOUT) . '，功能码=' . $funcode
            );
        }
        if ($errno !== 0) {
            throw new IpaynowException(
                IpaynowErrorCode::HTTP_EXCEPTION,
                '现在支付HTTP请求异常，功能码=' . $funcode . '，原因=' . $errmsg
            );
        }
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new IpaynowException(
                IpaynowErrorCode::HTTP_EXCEPTION,
                '现在支付HTTP请求失败，状态码=' . $statusCode . '，功能码=' . $funcode . '，响应报文=' . $body
            );
        }
        return $body === false ? '' : $body;
    }
}
