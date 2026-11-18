# Changelog

## 1.0.1 - 2026-11-18

- 支付结果回调（N001）成功判定兼容 `tradeStatus`：部分通道（如支付宝 APP 支付）只下发 `tradeStatus`，`isSuccess()` 在 `transStatus` 缺失时回退读取 `tradeStatus`；两者同时存在时以 `transStatus` 为准。
- 支付结果回调新增交易状态访问器（Java `getTradeStatus()`、Python `trade_status()`、Go `TradeStatus()`、Node.js `tradeStatus()`、PHP `getTradeStatus()`、.NET `TradeStatus`），并提供最终判定值访问器（Go `Status()`、Node.js `status()`、PHP `status()`、.NET `Status`）。
- Java 修正 `RefundNotifyResponse.getTransStatus()` 字段名误写问题：新增 `getTradeStatus()`，原方法保留并标记 `@Deprecated`。
- Java、Python、Node.js、PHP、.NET 包版本升级到 `1.0.1`；Go 通过 `v1.0.1` tag 标识版本。

## 1.0.0 - 2026-07-03

- 初始商用版本：支持统一下单、订单查询、退款、退款查询、撤销、撤销查询、支付回调验签和退款回调验签。
- 新增 `IpaynowClient.buildFormFields`，用于生成前端 form 表单提交字段和签名。
- 统一响应基础能力：`code()`、`message()`、`success()`、`rawBody()`、`fields()`。
- 下单响应补充 `getTn()` 和 `getNowPayOrderNo()`。
- 退款、撤销、退款查询、撤销查询按 `responseCode == R000` 判断成功。
- 请求规格持有网关路径和签名策略，退款/撤销链路自动排除 `signType` 参与签名。
- 参数对象支持 `extraParams` 扩展字段，并禁止覆盖 SDK 保留字段。
- 回调通知缺失签名或验签失败时抛出结构化 `IpaynowException`。
- 补充 README、CHANGELOG、LICENSE、Maven 元数据、sources jar 和 javadoc jar 配置。
