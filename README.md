# iPayNow PHP SDK

现在支付 PHP SDK，封装统一下单（JSAPI/APP/H5/小程序/聚合码）、订单查询、退款、撤销、退款/撤销查询、回调验签和前端表单字段生成。

## 环境要求

- PHP 7.3+
- ext-curl（默认 `CurlTransport` 基于 cURL 扩展；也可以自行实现 `HttpTransportInterface` 替换）

## 安装方式

```bash
composer require ipaynow/ipaynow-php-sdk
```

也可以不使用 Composer，直接注册 PSR-4 自动加载：

```php
spl_autoload_register(function ($class) {
    $prefix = 'Ipaynow\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    require __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
});
```

## 快速开始

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ipaynow\Domain\H5UnifiedOrderParams;
use Ipaynow\IpaynowClient;
use Ipaynow\IpaynowConfig;
use Ipaynow\Request\H5UnifiedOrderRequest;

$config = new IpaynowConfig('appId', 'appKey', 'https://pay.ipaynow.cn');
$client = new IpaynowClient($config);

$params = new H5UnifiedOrderParams(array(
    'mhtOrderNo' => 'ORDER202607030001',
    'mhtOrderName' => '测试订单',
    'mhtOrderDetail' => '测试订单',
    'mhtOrderAmt' => 1,
    'mhtOrderTimeOut' => 120,
    'mhtOrderStartTime' => '20260703120000',
    'notifyUrl' => 'https://example.com/notify',
    'frontNotifyUrl' => 'https://example.com/front',
    'payChannelType' => '12',
    'outputType' => '1',
    'consumerCreateIp' => '127.0.0.1',
));

$response = $client->execute(new H5UnifiedOrderRequest($params));
if ($response->isSuccess()) {
    $tn = $response->getTn();
}
```

Params 类构造函数接收关联数组：数组的 key 必须是类属性名（未在数组中出现的字段沿用类属性默认值）。

## 前端 Form 表单提交

需要让浏览器直接向现在支付网关提交表单时，使用 `buildFormFields` 生成完整请求字段。入参与 `execute` 一致，SDK 不发起 HTTP 请求。

```php
$fields = $client->buildFormFields(new H5UnifiedOrderRequest($params));
```

返回字段包含业务参数、公共参数和签名字段，例如：

- `funcode`
- `version`
- `appId`
- `mhtCharset`
- `mhtSignType` 或 `signType`
- `mhtSignature`

表单提交地址由请求路径决定：

```php
$action = $config->endpoint((new H5UnifiedOrderRequest($params))->path());
```

## 支持接口

| funcode | 说明 | Params | Request |
| --- | --- | --- | --- |
| `WP001` | JSAPI 统一下单 | `JsapiUnifiedOrderParams` | `JsapiUnifiedOrderRequest` |
| `WP001` | APP 统一下单 | `AppUnifiedOrderParams` | `AppUnifiedOrderRequest` |
| `WP001` | H5 统一下单 | `H5UnifiedOrderParams` | `H5UnifiedOrderRequest` |
| `WP001` | 小程序统一下单 | `MiniProgramUnifiedOrderParams` | `MiniProgramUnifiedOrderRequest` |
| `WP001` | 聚合码统一下单 | `AggregateCodeUnifiedOrderParams` | `AggregateCodeUnifiedOrderRequest` |
| `MQ002` | 订单查询 | `OrderQueryParams` | `OrderQueryRequest` |
| `R001` | 退款 | `RefundParams` | `RefundRequest` |
| `Q001` | 退款查询 | `RefundQueryParams` | `RefundQueryRequest` |
| `R002` | 撤销 | `ReverseParams` | `ReverseRequest` |
| `Q002` | 撤销查询 | `ReverseQueryParams` | `ReverseQueryRequest` |
| `N001` | 支付结果回调 | - | `NotifyParser::parsePayment` |
| `RN001` | 退款结果回调 | - | `NotifyParser::parseRefund` |

退款、退款查询、撤销、撤销查询请求会分别 POST 到独立路径（`/refund/refundOrder`、`/refund/cancelOrder`），而不是统一下单和订单查询使用的根路径 `/`，路径由每个 Request 内部决定，调用方无需关心。

## 响应判断

`IpaynowResponse::success()` 是 `isSuccess()` 的别名，各操作按以下规则判断成功：

- 统一下单（JSAPI/APP/H5/小程序/聚合码）、订单查询：`responseCode === 'A001'`（继承自 `IpaynowResponse` 默认实现）。
- 退款、退款查询、撤销、撤销查询：`responseCode === 'R000'`（`RefundResponse`/`RefundQueryResponse`/`ReverseResponse`/`ReverseQueryResponse` 各自覆盖 `isSuccess()`）。
- 支付结果回调 `PaymentNotifyResponse`：`transStatus === 'A001'`。
- 退款结果回调 `RefundNotifyResponse`：`tradeStatus === 'A001'`。

也可以直接读取原始字段：

```php
$code = $response->code();       // responseCode
$message = $response->message(); // responseMsg
$rawBody = $response->rawBody(); // 原始表单串
$fields = $response->fields();   // 全部字段
$value = $response->get('nowPayOrderNo');
```

统一下单响应（`UnifiedOrderResponse` 及其子类）额外提供：

```php
$tn = $response->getTn();                    // 支付要素，兼容大写 TN 字段回退
$orderNo = $response->getNowPayOrderNo();    // 现在支付流水号
```

订单查询响应 `OrderQueryResponse` 额外提供 `getTransStatus()`、`getNowPayOrderNo()`、`getPayTime()`、`getChannelOrderNo()`。

## 扩展字段

参数对象构造时可传入 `extraParams` 数组，用于透传 SDK 未显式声明的业务字段：

```php
$params = new H5UnifiedOrderParams(array(
    'mhtOrderNo' => 'O1',
    'mhtOrderAmt' => 1,
    'extraParams' => array('customField' => 'v1'),
));
```

`extraParams` 在命名字段之后合并进最终请求参数，但不能覆盖 SDK 保留字段，否则 `toMap()` 会抛出 `InvalidArgumentException`。保留字段：

- `funcode`
- `version`
- `appId`
- `mhtCharset`
- `mhtSignType`
- `signType`
- `mhtSignature`
- `signature`

## 回调验签

```php
use Ipaynow\Notify\NotifyAck;
use Ipaynow\Notify\NotifyParser;

$notify = NotifyParser::parsePayment($body, $appKey);
if ($notify->isSuccess()) {
    echo NotifyAck::success(); // success=Y
} else {
    echo NotifyAck::fail();    // success=N
}
```

退款回调：

```php
$notify = NotifyParser::parseRefund($body, $appKey);
```

验签失败时 `NotifyParser::parsePayment` / `parseRefund` 会抛出 `IpaynowException`（错误码 `IpaynowErrorCode::SIGN_ERROR`）。

## 错误处理

SDK 统一抛出 `Ipaynow\IpaynowException`，通过 `getErrorCode()` 区分具体失败类型，无需解析异常消息：

```php
use Ipaynow\IpaynowErrorCode;
use Ipaynow\IpaynowException;

try {
    $response = $client->execute(new H5UnifiedOrderRequest($params));
} catch (IpaynowException $e) {
    switch ($e->getErrorCode()) {
        case IpaynowErrorCode::CONNECT_TIMEOUT:
            // 建连超时
            break;
        case IpaynowErrorCode::SOCKET_TIMEOUT:
            // 读超时
            break;
        case IpaynowErrorCode::HTTP_EXCEPTION:
            // 非 2xx 响应，消息中含状态码与响应体
            break;
        case IpaynowErrorCode::SIGN_ERROR:
            // 响应或回调验签失败
            break;
        case IpaynowErrorCode::SYSTEM_ERROR:
            // SDK 内部错误（如响应类定义不合法）
            break;
    }
}
```

错误码表：

| 错误码 | 说明 |
| --- | --- |
| `E0001` | 组件内部异常 |
| `E0002` | HTTP 异常（含连接失败、非 2xx 响应等） |
| `E0003` | 建立连接时超时 |
| `E0004` | 等待或读取响应时超时 |
| `E0012` | 同步响应或回调通知签名缺失或验签失败 |
