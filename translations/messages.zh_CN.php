<?php

declare(strict_types=1);

return [
    'payment.number' => '交易编号',
    'payment.title' => '交易标题',
    'payment.amount' => '支付金额',
    'payment.refund_amount' => '退款金额',
    'payment.type' => '交易类型',
    'payment.gateway' => '支付方式',
    'payment.gateway.alipay_page' => '支付宝扫码支付',
    'payment.gateway.wxpay_app' => '微信 App 支付',
    'payment.gateway.wxpay_jsapi' => '微信 Jsapi 支付',
    'payment.gateway.wxpay_native' => '微信 Native 支付',
    'payment.state' => '支付状态',
    'payment.state.pending' => '待支付',
    'payment.state.succeed' => '支付成功',
    'payment.state.failed' => '支付失败',
    'payment.state.cancelled' => '已取消',
];
