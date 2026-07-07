<?php

namespace Ipaynow;

final class IpaynowClient
{
    /** @var IpaynowConfig */
    private $config;
    /** @var HttpTransportInterface */
    private $transport;

    public function __construct(IpaynowConfig $config, HttpTransportInterface $transport = null)
    {
        $this->config = $config;
        $this->transport = $transport !== null
            ? $transport
            : new CurlTransport($config->connectTimeoutMillis(), $config->readTimeoutMillis());
    }

    /**
     * 组装公共字段 -> 签名 -> POST 表单 -> 解析响应 -> 验签 -> 返回类型化 Response。
     *
     * @param SpecRequest $request
     * @return IpaynowResponse
     */
    public function execute(SpecRequest $request)
    {
        $funcode = $request->funcode();
        $excludeSignType = $request->excludeSignType();
        $signedFields = $this->signedFields($request);

        // HttpTransportInterface 实现会按错误类型直接抛出 IpaynowException，这里不再二次包装。
        $responseText = $this->transport->post($this->config->endpoint($request->path()), Signer::clean($signedFields));

        $fields = Parser::parseResponse($responseText);
        if (!Signer::verify($fields, $this->config, $request->responseSignatureField(), $excludeSignType)) {
            throw new IpaynowException(
                IpaynowErrorCode::SIGN_ERROR,
                '现在支付响应验签失败，功能码=' . $funcode
            );
        }

        $responseClass = $request->responseClass();
        try {
            $response = new $responseClass();
        } catch (\Throwable $e) {
            throw new IpaynowException(
                IpaynowErrorCode::SYSTEM_ERROR,
                '响应类必须提供公开无参构造方法: ' . $responseClass,
                $e
            );
        }
        if (!$response instanceof IpaynowResponse) {
            throw new IpaynowException(
                IpaynowErrorCode::SYSTEM_ERROR,
                '响应类必须继承 IpaynowResponse: ' . $responseClass
            );
        }
        $response->load($fields, $responseText);
        return $response;
    }

    /**
     * 返回签名后的完整表单字段（供前端跳转表单场景），不发请求。
     *
     * @param SpecRequest $request
     * @return array<string,string>
     */
    public function buildFormFields(SpecRequest $request)
    {
        return $this->signedFields($request);
    }

    /**
     * @return array<string,string>
     */
    private function signedFields(SpecRequest $request)
    {
        $params = $request->params();
        $params['funcode'] = $request->funcode();
        $params['version'] = $request->version();
        $params['appId'] = $this->config->appId();
        $params['mhtCharset'] = $request->mhtCharset();
        $params[$request->signTypeField()] = $this->config->signType();
        $params[$request->signatureField()] = Signer::sign(
            $params,
            $this->config,
            $request->excludeSignType(),
            array($request->signatureField(), $request->responseSignatureField())
        );
        return Signer::clean($params);
    }
}
