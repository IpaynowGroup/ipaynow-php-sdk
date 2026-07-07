<?php

/**
 * 纯 PHP 断言脚本，无 composer 外部依赖。覆盖规范 §10 的 8 项测试要求。
 * 用法：php tests/run.php
 * 失败时以非零退出码结束。
 */

spl_autoload_register(function ($class) {
    $prefix = 'Ipaynow\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use Ipaynow\Domain\AggregateCodeUnifiedOrderParams;
use Ipaynow\Domain\AppUnifiedOrderParams;
use Ipaynow\Domain\H5UnifiedOrderParams;
use Ipaynow\Domain\JsapiUnifiedOrderParams;
use Ipaynow\Domain\MiniProgramUnifiedOrderParams;
use Ipaynow\Domain\OrderQueryParams;
use Ipaynow\Domain\RefundParams;
use Ipaynow\Domain\RefundQueryParams;
use Ipaynow\Domain\ReverseParams;
use Ipaynow\Domain\ReverseQueryParams;
use Ipaynow\HttpTransportInterface;
use Ipaynow\IpaynowClient;
use Ipaynow\IpaynowConfig;
use Ipaynow\IpaynowErrorCode;
use Ipaynow\IpaynowException;
use Ipaynow\SignType;
use Ipaynow\Notify\NotifyAck;
use Ipaynow\Notify\NotifyParser;
use Ipaynow\Request\AggregateCodeUnifiedOrderRequest;
use Ipaynow\Request\AppUnifiedOrderRequest;
use Ipaynow\Request\H5UnifiedOrderRequest;
use Ipaynow\Request\JsapiUnifiedOrderRequest;
use Ipaynow\Request\MiniProgramUnifiedOrderRequest;
use Ipaynow\Request\OrderQueryRequest;
use Ipaynow\Request\RefundQueryRequest;
use Ipaynow\Request\RefundRequest;
use Ipaynow\Request\ReverseQueryRequest;
use Ipaynow\Request\ReverseRequest;
use Ipaynow\Response\AggregateCodeUnifiedOrderResponse;
use Ipaynow\Response\AppUnifiedOrderResponse;
use Ipaynow\Response\H5UnifiedOrderResponse;
use Ipaynow\Response\JsapiUnifiedOrderResponse;
use Ipaynow\Response\MiniProgramUnifiedOrderResponse;
use Ipaynow\Response\PaymentNotifyResponse;
use Ipaynow\Response\RefundNotifyResponse;
use Ipaynow\Response\RefundQueryResponse;
use Ipaynow\Response\RefundResponse;
use Ipaynow\Response\ReverseQueryResponse;
use Ipaynow\Response\ReverseResponse;
use Ipaynow\Signer;

$GLOBALS['__pass'] = 0;
$GLOBALS['__fail'] = 0;
$GLOBALS['__failures'] = array();
$GLOBALS['md5Config'] = newMd5Config();
$md5Config = $GLOBALS['md5Config'];

function assertTrue($cond, $message)
{
    if ($cond) {
        $GLOBALS['__pass']++;
    } else {
        $GLOBALS['__fail']++;
        $GLOBALS['__failures'][] = $message;
        fwrite(STDERR, "FAIL: $message\n");
    }
}

function assertEquals($expected, $actual, $message)
{
    assertTrue($expected === $actual, "$message (expected: " . var_export($expected, true) . ", actual: " . var_export($actual, true) . ')');
}

function assertThrowsErrorCode($errorCode, callable $fn, $message)
{
    try {
        $fn();
        assertTrue(false, "$message (未抛出异常)");
    } catch (IpaynowException $e) {
        assertEquals($errorCode, $e->getErrorCode(), $message);
    }
}

function assertThrows($exceptionClass, callable $fn, $message)
{
    try {
        $fn();
        assertTrue(false, "$message (未抛出异常)");
    } catch (\Throwable $e) {
        assertTrue($e instanceof $exceptionClass, "$message (异常类型不符: " . get_class($e) . ')');
    }
}

final class FakeTransport implements HttpTransportInterface
{
    public $called = false;
    public $url;
    public $params;
    private $response;
    private $exceptionToThrow;

    public function __construct($response = '', \Throwable $exceptionToThrow = null)
    {
        $this->response = $response;
        $this->exceptionToThrow = $exceptionToThrow;
    }

    public function post($url, array $params)
    {
        $this->called = true;
        $this->url = $url;
        $this->params = $params;
        if ($this->exceptionToThrow !== null) {
            throw $this->exceptionToThrow;
        }
        return $this->response;
    }
}

function newMd5Config()
{
    return new IpaynowConfig('app', 'secret', null, null, SignType::MD5, 'https://pay.test');
}

function newRsaConfig()
{
    $resource = openssl_pkey_new(array('private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA));
    openssl_pkey_export($resource, $privatePem);
    $details = openssl_pkey_get_details($resource);
    $privateBase64 = preg_replace('/\s+/', '', str_replace(array(
        '-----BEGIN PRIVATE KEY-----',
        '-----END PRIVATE KEY-----',
    ), '', $privatePem));
    $publicBase64 = preg_replace('/\s+/', '', str_replace(array(
        '-----BEGIN PUBLIC KEY-----',
        '-----END PUBLIC KEY-----',
    ), '', $details['key']));
    return new IpaynowConfig('app', null, $privateBase64, $publicBase64, SignType::RSA, 'https://pay.test');
}

// ---------------------------------------------------------------------
// 1. 签名：排序/剔除空值/剔除签名字段；excludeSignType 规则；已知向量
// ---------------------------------------------------------------------

$withSignType = array('mhtOrderNo' => 'O1', 'signType' => 'MD5');
$withoutSignType = array('mhtOrderNo' => 'O1');
assertEquals(
    Signer::sign($withoutSignType, $md5Config, true),
    Signer::sign($withSignType, $md5Config, true),
    'excludeSignType=true 时应剔除 signType'
);
assertTrue(
    Signer::sign($withoutSignType, $md5Config, false) !== Signer::sign($withSignType, $md5Config, false),
    'excludeSignType=false 时应保留 signType'
);

$withMhtSignType = array('mhtOrderNo' => 'O1', 'mhtSignType' => 'MD5');
$without = array('mhtOrderNo' => 'O1');
assertTrue(
    Signer::sign($without, $md5Config, true) !== Signer::sign($withMhtSignType, $md5Config, true),
    'mhtSignType 应始终参与签名（excludeSignType=true）'
);
assertTrue(
    Signer::sign($without, $md5Config, false) !== Signer::sign($withMhtSignType, $md5Config, false),
    'mhtSignType 应始终参与签名（excludeSignType=false）'
);

$vectorParams = array(
    'b' => '2',
    'a' => '1',
    'empty' => '',
    'signType' => 'MD5',
    'mhtSignature' => 'bad',
);
assertEquals(
    'c319c9529d781b179bff6bfdc3f82202',
    Signer::sign($vectorParams, $md5Config, true),
    '规范 §10.1 已知签名向量'
);

// ---------------------------------------------------------------------
// 2. 验签：缺失->false；正向 exclude=false 通过 / true 拒绝；退款 true 通过 / false 拒绝
// ---------------------------------------------------------------------

$missingSig = array('responseCode' => 'A001');
assertTrue(Signer::verify($missingSig, $md5Config, 'signature', false) === false, '签名缺失应验签失败');

$forwardFields = array('responseCode' => 'A001', 'signType' => 'MD5');
$forwardFields['signature'] = Signer::sign($forwardFields, $md5Config, false);
assertTrue(Signer::verify($forwardFields, $md5Config, 'signature', false) === true, '正向响应 exclude=false 应通过');
assertTrue(Signer::verify($forwardFields, $md5Config, 'signature', true) === false, '正向响应 exclude=true 应拒绝');

$refundFields = array('mhtRefundNo' => 'RF1', 'signType' => 'MD5');
$refundFields['signature'] = Signer::sign($refundFields, $md5Config, true);
assertTrue(Signer::verify($refundFields, $md5Config, 'signature', true) === true, '退款响应 exclude=true 应通过');
assertTrue(Signer::verify($refundFields, $md5Config, 'signature', false) === false, '退款响应 exclude=false 应拒绝');

$wrongSig = array('responseCode' => 'A001', 'signature' => 'deadbeefdeadbeefdeadbeefdeadbeef');
assertTrue(Signer::verify($wrongSig, $md5Config, 'signature', false) === false, '错误签名应验签失败');

// ---------------------------------------------------------------------
// 3. execute：公共字段组装、验签失败抛 SIGN_ERROR、正常路径返回类型化 Response
// ---------------------------------------------------------------------

$config = new IpaynowConfig('app', 'secret', null, null, SignType::MD5, 'https://pay.test');

$responseFields = array('responseCode' => 'A001', 'nowPayOrderNo' => 'N1');
$signature = Signer::sign($responseFields, $config, false);
$body = 'responseCode=A001&nowPayOrderNo=N1&signature=' . $signature;
$transport = new FakeTransport($body);
$client = new IpaynowClient($config, $transport);

$jsapiParams = new JsapiUnifiedOrderParams(array('mhtOrderNo' => 'O1', 'mhtOrderAmt' => 1));
/** @var JsapiUnifiedOrderResponse $response */
$response = $client->execute(new JsapiUnifiedOrderRequest($jsapiParams));

assertEquals('https://pay.test/', $transport->url, 'endpoint 应为 baseUrl + path');
assertEquals('app', $transport->params['appId'], '公共字段 appId');
assertEquals('WP001', $transport->params['funcode'], '公共字段 funcode');
assertEquals('1.0.0', $transport->params['version'], '公共字段 version');
assertEquals('MD5', $transport->params['mhtSignType'], '公共字段 mhtSignType');
assertTrue(isset($transport->params['mhtSignature']) && $transport->params['mhtSignature'] !== '', '公共字段 mhtSignature 存在');
assertTrue($response instanceof JsapiUnifiedOrderResponse, 'execute 返回类型化 Response');
assertEquals('A001', $response->code(), 'response->code()');
assertEquals('N1', $response->get('nowPayOrderNo'), 'response->get()');
assertEquals($body, $response->rawBody(), 'response->rawBody()');
assertTrue($response->isSuccess(), 'WP001 A001 应判定成功');

// buildFormFields 不发请求
$transport2 = new FakeTransport('unused');
$client2 = new IpaynowClient($config, $transport2);
$jsapiRequest = new JsapiUnifiedOrderRequest(new JsapiUnifiedOrderParams(array('mhtOrderNo' => 'O1', 'mhtOrderAmt' => 1)));
$fields = $client2->buildFormFields($jsapiRequest);
assertEquals('O1', $fields['mhtOrderNo'], 'buildFormFields 保留业务字段');
assertEquals('WP001', $fields['funcode'], 'buildFormFields funcode');
assertEquals('app', $fields['appId'], 'buildFormFields appId');
assertEquals('UTF-8', $fields['mhtCharset'], 'buildFormFields mhtCharset');
assertEquals('MD5', $fields['mhtSignType'], 'buildFormFields mhtSignType');
assertTrue(!$transport2->called, 'buildFormFields 不应调用 transport');

// 验签失败抛 SIGN_ERROR
$transportBadSig = new FakeTransport('responseCode=A001&nowPayOrderNo=N1');
$clientBadSig = new IpaynowClient($config, $transportBadSig);
assertThrowsErrorCode(
    IpaynowErrorCode::SIGN_ERROR,
    function () use ($clientBadSig) {
        $clientBadSig->execute(new JsapiUnifiedOrderRequest(new JsapiUnifiedOrderParams(array('mhtOrderNo' => 'O1', 'mhtOrderAmt' => 1))));
    },
    'execute 响应验签缺失应抛 SIGN_ERROR'
);

// 退款请求走独立 path 与 excludeSignType 策略
$refundResponseFields = array('responseCode' => 'A001');
$refundSig = Signer::sign($refundResponseFields, $md5Config, true);
$refundTransport = new FakeTransport('responseCode=A001&signature=' . $refundSig);
$refundClient = new IpaynowClient(new IpaynowConfig('app', 'secret', null, null, SignType::MD5, 'https://pay.test/'), $refundTransport);
$refundParams = new RefundParams(array('mhtOrderNo' => 'O1', 'mhtRefundNo' => 'R1', 'amount' => 1));
$refundClient->execute(new RefundRequest($refundParams));
assertEquals('https://pay.test/refund/refundOrder', $refundTransport->url, '退款请求应走 /refund/refundOrder 路径');

// ---------------------------------------------------------------------
// 4. 每操作 Request：funcode/version/path/signTypeField/excludeSignType 与 §2 一致；
//    Params 默认值与 §5 一致
// ---------------------------------------------------------------------

function assertRequestMeta($request, $funcode, $version, $path, $signTypeField, $excludeSignType, $label)
{
    assertEquals($funcode, $request->funcode(), "$label funcode");
    assertEquals($version, $request->version(), "$label version");
    assertEquals($path, $request->path(), "$label path");
    assertEquals($signTypeField, $request->signTypeField(), "$label signTypeField");
    assertEquals($excludeSignType, $request->excludeSignType(), "$label excludeSignType");
    assertEquals('mhtSignature', $request->signatureField(), "$label signatureField");
    assertEquals('signature', $request->responseSignatureField(), "$label responseSignatureField");
}

assertRequestMeta(new JsapiUnifiedOrderRequest(new JsapiUnifiedOrderParams()), 'WP001', '1.0.0', '/', 'mhtSignType', false, 'JsapiUnifiedOrderRequest');
assertRequestMeta(new AggregateCodeUnifiedOrderRequest(new AggregateCodeUnifiedOrderParams()), 'WP001', '1.0.3', '/', 'mhtSignType', false, 'AggregateCodeUnifiedOrderRequest');
assertRequestMeta(new MiniProgramUnifiedOrderRequest(new MiniProgramUnifiedOrderParams()), 'WP001', '1.0.0', '/', 'mhtSignType', false, 'MiniProgramUnifiedOrderRequest');
assertRequestMeta(new H5UnifiedOrderRequest(new H5UnifiedOrderParams()), 'WP001', '1.0.0', '/', 'mhtSignType', false, 'H5UnifiedOrderRequest');
assertRequestMeta(new AppUnifiedOrderRequest(new AppUnifiedOrderParams()), 'WP001', '1.0.3', '/', 'mhtSignType', false, 'AppUnifiedOrderRequest');
assertRequestMeta(new OrderQueryRequest(new OrderQueryParams(array('mhtOrderNo' => 'O1'))), 'MQ002', '1.0.0', '/', 'mhtSignType', false, 'OrderQueryRequest');
assertRequestMeta(new OrderQueryRequest(new OrderQueryParams(array('mhtOrderNo' => 'O1', 'version' => '2.0.0'))), 'MQ002', '2.0.0', '/', 'mhtSignType', false, 'OrderQueryRequest(version覆盖)');
assertRequestMeta(new RefundRequest(new RefundParams()), 'R001', '1.0.0', '/refund/refundOrder', 'signType', true, 'RefundRequest');
assertRequestMeta(new RefundQueryRequest(new RefundQueryParams()), 'Q001', '1.0.0', '/refund/refundQuery', 'signType', true, 'RefundQueryRequest');
assertRequestMeta(new ReverseRequest(new ReverseParams()), 'R002', '1.0.0', '/refund/cancelOrder', 'signType', true, 'ReverseRequest');
assertRequestMeta(new ReverseQueryRequest(new ReverseQueryParams()), 'Q002', '1.0.0', '/refund/refundQuery', 'signType', true, 'ReverseQueryRequest');

// Params 默认值
$jsapiDefaults = (new JsapiUnifiedOrderParams())->toMap();
assertEquals('0600', $jsapiDefaults['deviceType'], 'JsapiUnifiedOrderParams.deviceType 默认值');
assertEquals('156', $jsapiDefaults['mhtCurrencyType'], 'JsapiUnifiedOrderParams.mhtCurrencyType 默认值');
assertEquals('120', $jsapiDefaults['mhtOrderTimeOut'], 'JsapiUnifiedOrderParams.mhtOrderTimeOut 默认值');
assertEquals('05', $jsapiDefaults['mhtOrderType'], 'JsapiUnifiedOrderParams.mhtOrderType 默认值');

$aggDefaults = (new AggregateCodeUnifiedOrderParams())->toMap();
assertEquals('20', $aggDefaults['deviceType'], 'AggregateCodeUnifiedOrderParams.deviceType 默认值');

$miniDefaults = (new MiniProgramUnifiedOrderParams())->toMap();
assertEquals('14', $miniDefaults['deviceType'], 'MiniProgramUnifiedOrderParams.deviceType 默认值');
assertEquals('1', $miniDefaults['outputType'], 'MiniProgramUnifiedOrderParams.outputType 默认值');
$miniDocFields = (new MiniProgramUnifiedOrderParams(array('mhtStoreId' => 'S1')))->toMap();
assertEquals('S1', $miniDocFields['mhtStoreId'], 'MiniProgramUnifiedOrderParams.mhtStoreId');

$h5Defaults = (new H5UnifiedOrderParams())->toMap();
assertEquals('0601', $h5Defaults['deviceType'], 'H5UnifiedOrderParams.deviceType 默认值');
$h5DocFields = (new H5UnifiedOrderParams(array('specifiedChannel' => 'wechat', 'payAccNo' => '18800000000', 'guideId' => 'G1')))->toMap();
assertEquals('wechat', $h5DocFields['specifiedChannel'], 'H5UnifiedOrderParams.specifiedChannel');
assertEquals('18800000000', $h5DocFields['payAccNo'], 'H5UnifiedOrderParams.payAccNo');
assertEquals('G1', $h5DocFields['guideId'], 'H5UnifiedOrderParams.guideId');

$appDefaults = (new AppUnifiedOrderParams())->toMap();
assertEquals('01', $appDefaults['deviceType'], 'AppUnifiedOrderParams.deviceType 默认值');
assertEquals('0', $appDefaults['outputType'], 'AppUnifiedOrderParams.outputType 默认值');
$appDocFields = (new AppUnifiedOrderParams(array('frontNotifyUrl' => 'https://example.com/front', 'mhtStoreId' => 'S1')))->toMap();
assertEquals('https://example.com/front', $appDocFields['frontNotifyUrl'], 'AppUnifiedOrderParams.frontNotifyUrl');
assertEquals('S1', $appDocFields['mhtStoreId'], 'AppUnifiedOrderParams.mhtStoreId');

// extraParams 透传，且不能覆盖保留字段
$withExtra = new JsapiUnifiedOrderParams(array('mhtOrderNo' => 'O1', 'extraParams' => array('customField' => 'v1')));
assertEquals('v1', $withExtra->toMap()['customField'], 'extraParams 透传自定义字段');
assertThrows(
    \InvalidArgumentException::class,
    function () {
        (new JsapiUnifiedOrderParams(array('extraParams' => array('funcode' => 'X'))))->toMap();
    },
    'extraParams 不能覆盖保留字段 funcode'
);

// ---------------------------------------------------------------------
// 5. NotifyParser：N001 exclude=false、RN001 exclude=true；验签失败抛 SIGN_ERROR
// ---------------------------------------------------------------------

$paymentFields = array('responseCode' => 'A001', 'mhtOrderNo' => 'O1');
$paymentFields['signature'] = Signer::sign($paymentFields, $md5Config, false);
$paymentBody = 'responseCode=A001&mhtOrderNo=O1&signature=' . $paymentFields['signature'];
$paymentResponse = NotifyParser::parsePayment($paymentBody, $md5Config);
assertTrue($paymentResponse instanceof PaymentNotifyResponse, 'parsePayment 返回 PaymentNotifyResponse');
assertEquals('A001', $paymentResponse->get('responseCode'), 'parsePayment 字段解析');

$refundNotifyFields = array('tradeStatus' => 'A001', 'mhtRefundNo' => 'R1');
$refundNotifyFields['signature'] = Signer::sign($refundNotifyFields, $md5Config, true);
$refundNotifyBody = 'tradeStatus=A001&mhtRefundNo=R1&signature=' . $refundNotifyFields['signature'];
$refundNotifyResponse = NotifyParser::parseRefund($refundNotifyBody, $md5Config);
assertTrue($refundNotifyResponse instanceof RefundNotifyResponse, 'parseRefund 返回 RefundNotifyResponse');
assertTrue($refundNotifyResponse->isSuccess(), 'parseRefund tradeStatus=A001 应成功');

assertThrowsErrorCode(
    IpaynowErrorCode::SIGN_ERROR,
    function () {
        NotifyParser::parsePayment('responseCode=A001&mhtOrderNo=O1', $GLOBALS['md5Config']);
    },
    'parsePayment 缺失签名应抛 SIGN_ERROR'
);
assertThrowsErrorCode(
    IpaynowErrorCode::SIGN_ERROR,
    function () {
        NotifyParser::parseRefund('tradeStatus=A001', $GLOBALS['md5Config']);
    },
    'parseRefund 缺失签名应抛 SIGN_ERROR'
);

// N001 用 RN001 的排除规则验签必须失败（交叉验证 exclude 规则确实生效）
assertThrowsErrorCode(
    IpaynowErrorCode::SIGN_ERROR,
    function () {
        $fields = array('responseCode' => 'A001', 'signType' => 'MD5');
        $fields['signature'] = Signer::sign($fields, $GLOBALS['md5Config'], false); // 按正向规则签名
        NotifyParser::parseRefund('responseCode=A001&signType=MD5&signature=' . $fields['signature'], $GLOBALS['md5Config']); // 按退款规则验签
    },
    'signType 参与/排除规则不一致时验签应失败'
);

// NotifyAck
assertEquals('success=Y', NotifyAck::success(), 'NotifyAck::success()');
assertEquals('success=N', NotifyAck::fail(), 'NotifyAck::fail()');

// ---------------------------------------------------------------------
// 6. isSuccess：§6 全部 5 种规则
// ---------------------------------------------------------------------

function newLoadedResponse($class, array $fields)
{
    $response = new $class();
    $response->load($fields);
    return $response;
}

foreach (array(
    JsapiUnifiedOrderResponse::class,
    AppUnifiedOrderResponse::class,
    H5UnifiedOrderResponse::class,
    MiniProgramUnifiedOrderResponse::class,
    AggregateCodeUnifiedOrderResponse::class,
) as $unifiedClass) {
    $success = newLoadedResponse($unifiedClass, array('responseCode' => 'A001', 'tn' => 'TN001'));
    $failure = newLoadedResponse($unifiedClass, array('responseCode' => 'A002'));
    assertTrue($success->isSuccess(), "$unifiedClass responseCode=A001 应成功");
    assertTrue(!$failure->isSuccess(), "$unifiedClass responseCode=A002 应失败");
    assertEquals('TN001', $success->getTn(), "$unifiedClass getTn()");
}

$tnUpper = newLoadedResponse(JsapiUnifiedOrderResponse::class, array('TN' => 'TN002'));
assertEquals('TN002', $tnUpper->getTn(), 'getTn() 兼容大写 TN 回退');

$orderQuerySuccess = newLoadedResponse(\Ipaynow\Response\OrderQueryResponse::class, array('responseCode' => 'A001', 'transStatus' => 'S1', 'nowPayOrderNo' => 'N1', 'payTime' => 'T1', 'channelOrderNo' => 'C1'));
assertTrue($orderQuerySuccess->isSuccess(), 'OrderQueryResponse responseCode=A001 应成功');
assertEquals('S1', $orderQuerySuccess->getTransStatus(), 'OrderQueryResponse getTransStatus()');
assertEquals('N1', $orderQuerySuccess->getNowPayOrderNo(), 'OrderQueryResponse getNowPayOrderNo()');
assertEquals('T1', $orderQuerySuccess->getPayTime(), 'OrderQueryResponse getPayTime()');
assertEquals('C1', $orderQuerySuccess->getChannelOrderNo(), 'OrderQueryResponse getChannelOrderNo()');

foreach (array(
    RefundResponse::class,
    RefundQueryResponse::class,
    ReverseResponse::class,
    ReverseQueryResponse::class,
) as $refundClass) {
    $success = newLoadedResponse($refundClass, array('responseCode' => 'R000'));
    $failure = newLoadedResponse($refundClass, array('responseCode' => 'A001'));
    assertTrue($success->isSuccess(), "$refundClass responseCode=R000 应成功");
    assertTrue(!$failure->isSuccess(), "$refundClass responseCode=A001 应失败（非退款成功码）");
}

$paymentNotifySuccess = newLoadedResponse(PaymentNotifyResponse::class, array('transStatus' => 'A001'));
$paymentNotifyFailure = newLoadedResponse(PaymentNotifyResponse::class, array('transStatus' => 'A002'));
assertTrue($paymentNotifySuccess->isSuccess(), 'PaymentNotifyResponse transStatus=A001 应成功');
assertTrue(!$paymentNotifyFailure->isSuccess(), 'PaymentNotifyResponse transStatus=A002 应失败');

$refundNotifySuccess = newLoadedResponse(RefundNotifyResponse::class, array('tradeStatus' => 'A001'));
$refundNotifyFailure = newLoadedResponse(RefundNotifyResponse::class, array('tradeStatus' => 'A002'));
assertTrue($refundNotifySuccess->isSuccess(), 'RefundNotifyResponse tradeStatus=A001 应成功');
assertTrue(!$refundNotifyFailure->isSuccess(), 'RefundNotifyResponse tradeStatus=A002 应失败');

// ---------------------------------------------------------------------
// 7. Config 校验：空 appId/appKey/baseUrl、非正超时抛参数错误
// ---------------------------------------------------------------------

assertThrows(\InvalidArgumentException::class, function () {
    new IpaynowConfig(null, 'key');
}, 'appId 为 null 应抛出');
assertThrows(\InvalidArgumentException::class, function () {
    new IpaynowConfig('app', ' ');
}, 'appKey 纯空白应抛出');
assertThrows(\InvalidArgumentException::class, function () {
    new IpaynowConfig('app', 'key', null, null, SignType::MD5, '');
}, 'baseUrl 为空应抛出');
assertThrows(\InvalidArgumentException::class, function () {
    new IpaynowConfig('app', 'key', null, null, SignType::MD5, 'https://pay.test', 0, 15000);
}, 'connectTimeoutMillis 非正应抛出');
assertThrows(\InvalidArgumentException::class, function () {
    new IpaynowConfig('app', 'key', null, null, SignType::MD5, 'https://pay.test', 5000, -1);
}, 'readTimeoutMillis 非正应抛出');

$validConfig = new IpaynowConfig('app', 'key');
assertEquals('https://pay.ipaynow.cn', $validConfig->baseUrl(), 'baseUrl 默认值');
assertEquals(5000, $validConfig->connectTimeoutMillis(), 'connectTimeoutMillis 默认值');
assertEquals(15000, $validConfig->readTimeoutMillis(), 'readTimeoutMillis 默认值');
assertEquals('https://pay.ipaynow.cn/refund/refundOrder', $validConfig->endpoint('/refund/refundOrder'), 'endpoint 拼接');

// ---------------------------------------------------------------------
// 8. 传输层：非 2xx 抛 HTTP_EXCEPTION（含状态码和响应体）；读超时抛 SOCKET_TIMEOUT
// ---------------------------------------------------------------------

// 用注入的 transport 闭包模拟异常，验证 Client 对 IpaynowException 直接透传不二次包装
$httpFailTransport = new FakeTransport('', new IpaynowException(IpaynowErrorCode::HTTP_EXCEPTION, '现在支付HTTP请求失败，状态码=500，功能码=WP001，响应报文=oops'));
$httpFailClient = new IpaynowClient($config, $httpFailTransport);
assertThrowsErrorCode(
    IpaynowErrorCode::HTTP_EXCEPTION,
    function () use ($httpFailClient) {
        $httpFailClient->execute(new JsapiUnifiedOrderRequest(new JsapiUnifiedOrderParams(array('mhtOrderNo' => 'O1', 'mhtOrderAmt' => 1))));
    },
    'HTTP 非 2xx 应抛 HTTP_EXCEPTION'
);
try {
    $httpFailClient->execute(new JsapiUnifiedOrderRequest(new JsapiUnifiedOrderParams(array('mhtOrderNo' => 'O1', 'mhtOrderAmt' => 1))));
} catch (IpaynowException $e) {
    assertTrue(strpos($e->getMessage(), '500') !== false, 'HTTP_EXCEPTION 消息应含状态码');
    assertTrue(strpos($e->getMessage(), 'oops') !== false, 'HTTP_EXCEPTION 消息应含响应体');
}

$timeoutTransport = new FakeTransport('', new IpaynowException(IpaynowErrorCode::SOCKET_TIMEOUT, 'SocketTimeOut，功能码=WP001'));
$timeoutClient = new IpaynowClient($config, $timeoutTransport);
assertThrowsErrorCode(
    IpaynowErrorCode::SOCKET_TIMEOUT,
    function () use ($timeoutClient) {
        $timeoutClient->execute(new JsapiUnifiedOrderRequest(new JsapiUnifiedOrderParams(array('mhtOrderNo' => 'O1', 'mhtOrderAmt' => 1))));
    },
    '读超时应抛 SOCKET_TIMEOUT'
);

$connectTimeoutTransport = new FakeTransport('', new IpaynowException(IpaynowErrorCode::CONNECT_TIMEOUT, 'ConnectTimeOut，功能码=WP001'));
$connectTimeoutClient = new IpaynowClient($config, $connectTimeoutTransport);
assertThrowsErrorCode(
    IpaynowErrorCode::CONNECT_TIMEOUT,
    function () use ($connectTimeoutClient) {
        $connectTimeoutClient->execute(new JsapiUnifiedOrderRequest(new JsapiUnifiedOrderParams(array('mhtOrderNo' => 'O1', 'mhtOrderAmt' => 1))));
    },
    '建连超时应抛 CONNECT_TIMEOUT'
);

// 真实 cURL 传输层：用 PHP 内置 server 验证非 2xx 会抛 HTTP_EXCEPTION 且消息含状态码和响应体
if (function_exists('curl_init')) {
    $docroot = sys_get_temp_dir() . '/ipaynow-php-sdk-test-server-' . getmypid();
    @mkdir($docroot);
    file_put_contents($docroot . '/500.php', "<?php http_response_code(500); echo 'server-error-body';");
    $port = 18723 + (getmypid() % 500);
    $cmd = sprintf('php -S 127.0.0.1:%d -t %s > /dev/null 2>&1 & echo $!', $port, escapeshellarg($docroot));
    $pid = trim(shell_exec($cmd));
    usleep(300000); // 等待内置 server 就绪

    try {
        $curlTransport = new \Ipaynow\CurlTransport(2000, 2000);
        $threw = false;
        try {
            $curlTransport->post("http://127.0.0.1:$port/500.php", array('funcode' => 'WP001'));
        } catch (IpaynowException $e) {
            $threw = true;
            assertEquals(IpaynowErrorCode::HTTP_EXCEPTION, $e->getErrorCode(), 'CurlTransport 非 2xx 应抛 HTTP_EXCEPTION');
            assertTrue(strpos($e->getMessage(), '500') !== false, 'CurlTransport 消息应含状态码 500');
            assertTrue(strpos($e->getMessage(), 'server-error-body') !== false, 'CurlTransport 消息应含响应体');
        }
        assertTrue($threw, 'CurlTransport 非 2xx 应抛出异常');
    } finally {
        if ($pid !== '') {
            shell_exec('kill ' . escapeshellarg($pid) . ' 2>/dev/null');
        }
        @unlink($docroot . '/500.php');
        @rmdir($docroot);
    }

    // 连接一个不存在/不可路由的地址触发建连超时，验证 CONNECT_TIMEOUT 分类
    $unreachableTransport = new \Ipaynow\CurlTransport(200, 200);
    assertThrowsErrorCode(
        IpaynowErrorCode::CONNECT_TIMEOUT,
        function () use ($unreachableTransport) {
            // 10.255.255.1 为常用的不可路由测试地址，用于制造建连超时
            $unreachableTransport->post('http://10.255.255.1/', array('funcode' => 'WP001'));
        },
        'CurlTransport 对不可路由地址应判定为建连超时'
    );
} else {
    fwrite(STDERR, "跳过 cURL 真实传输层测试：当前环境没有 curl 扩展\n");
}

// ---------------------------------------------------------------------
// 汇总
// ---------------------------------------------------------------------

$pass = $GLOBALS['__pass'];
$fail = $GLOBALS['__fail'];
echo "通过: $pass, 失败: $fail\n";
if ($fail > 0) {
    exit(1);
}
exit(0);
