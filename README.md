# wepay
微信支付v3版 for PHP
----

建议在 PHP7.1 上运行以获取最佳性能；
WePay APIv3 SDK for PHP

功能描述
----

* 微信支付v3版--商户直连模式 WePay
* 微信支付v3版--服务商模式 WePayPartner
* 实现功能一、Order 下单、发起支付、查询订单、回调通知
* 实现功能二、Refund 创建退款订单、退款订单查询、获取退款通知

WePay 是基于官方（v3）接口封装，在做微信开发前，必需先阅读微信官方文档。

* 微信官方文档（商户）：https://pay.weixin.qq.com/wiki/doc/apiv3/wxpay/pages/index.shtml
* 微信官方文档（服务商）：https://pay.weixin.qq.com/wiki/doc/apiv3_partner/pages/index.shtml

安装使用
----
1.1 通过 Composer 来管理安装

```shell
# 首次安装 
composer require wander/wepay

# 更新 WePay
composer update wander/wepay
```

1.2 如果不使用 Composer， 可以下载 WePay 并解压到项目中

```php
# 在项目中加载初始化文件
include "您的目录/WePay/include.php";
```

2.1 接口实例所需参数

```php
$config = [
    // 商户绑定的公众号APPID
    'appid'        => 'wx97c0e0345878c0000',
    // 微信商户编号ID
    'mch_id'       => '1613190000',
    // 微信商户V3接口密钥
    'mch_v3_key'   => 'uioman4b6p5rcw189qfvy7zh3ekx0000',
    // 微信商户证书公钥，支持证书内容或文件路径
    'cert_public'  => $certPublic,
    // 微信商户证书私钥，支持证书内容或文件路径
    'cert_private' => $certPrivate,
    // 缓存目录配置（可选，需拥有读写权限）
    'cache_path'     => '',
];
```

3.1 商户直连模式创建订单

```php
try {
    //商户直连模式
    $payment = \WePay\Order::instance($config);
    // 组装支付参数
    $result = $payment->create('jsapi', [
        'appid'        => $config['appid'],
        'mchid'        => $config['mch_id'],
        'description'  => '商品描述',
        'out_trade_no' => date("YmdHis"),
        'notify_url'   => 'https://wander.pub',
        'payer'        => ['openid' => 'oUC2e6gByJEShMa_5gDOTz5x0000'],
        'amount'       => ['total' => 1, 'currency' => 'CNY'],
    ]);

    echo '<pre>';
    echo "\n--- 创建支付参数 ---\n";
    var_export($result);
    
} catch (Exception $e) {
    // 出错啦，处理下吧
    echo $e->getMessage() . PHP_EOL;
}
```

服务商模式
----

4.1 服务商模式--配置文件

```php
$config = [
    'sp_appid'     => 'wx5d65886837ce0000',//服务商APPID
    'sp_mchid'     => '1613090000',//服务商mchid
    'sub_appid'    => 'wx97c0e03458780000',//微信子商户编号ID
    'sub_mchid'    => '1613190000',// 微信子商户mchid
    'mch_v3_key'   => 'uioman4b6p5rcw189qfvy7zh3ekx0000',
    'cert_public'  => $certPublic,// 微信商户证书公钥，支持证书内容或文件路径
    'cert_private' => $certPrivate// 微信商户证书私钥，支持证书内容或文件路径
];
```

4.2 服务商模式--创建订单

```php
try {
    $payment = \WePayPartner\Order::instance($config);
    // 组装支付参数
    $result = $payment->create('jsapi', [
        'sp_appid'      => $config['sp_appid'],//服务商应用ID
        'sp_mchid'      => $config['sp_mchid'],//服务商户号
        'sub_appid'     => $config['sub_appid'],//子商户应用ID
        'sub_mchid'     => $config['sub_mchid'],//子商户号
        'description'   => '商品描述',//商品描述
        'attach'        => sprintf('{"order_id":%s,"type":1}', 110),//附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用
        'notify_url'    => 'https://wander.pub',//通知URL必须为直接可访问的URL，不允许携带查询串。
        'out_trade_no'  => '1222222222',//商户系统内部订单号，只能是数字、大小写字母_-*且在同一个商户号下唯一，详见【商户订单号】。特殊规则：最小字符长度为6
        'amount'        => ['total'=> 1],//订单总金额，单位为分
        'payer'         => ['sub_openid' => 'oUC2e6gByJEShMa_5gDOTz5x0000']//用户子标识openid
    ]);
    echo '<pre>';
    echo "\n--- 创建支付参数 ---\n";
    var_export($result);
} catch (\Exception $exception) {
    // 出错啦，处理下吧
    echo $exception->getMessage() . PHP_EOL;
}
```

开源协议
----

* WePay 基于`MIT`协议发布，任何人可以用在任何地方，不受约束
* WePay 部分代码来自互联网，若有异议，可以联系作者进行删除

