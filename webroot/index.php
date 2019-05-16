<?php
use Mofing\DoNewsPush\Push;

require dirname(__DIR__) . '/config/paths.php';
require dirname(__DIR__) . '/vendor/autoload.php';

try {
    // 测试发送
    $push = new Push();
    /* $result = $push->send("0862848040691494300003689500CN01", "阿宝天气提醒".time(), "推送的消息内容,点击查看详情", "huawei", "message", "go_custom", [
        ["type" => "typetypetype"],
        ["id" => "this is id"]
    ]); */
    
    $pushData = [
        'assort'=>8,
        'mtype'=>2,
        'title'=>"您的外卖订单,商家已经确认接单",
        'role'=>3,// 1:店主 2:骑手 3:买家
        'shop_id'=>2536184,
        'order_id'=>123456,
        'status' =>1,
        'sound' =>1,
    ];    
    //华为测试
    //$result = $push->send("0862848040691494300003689500CN01", "阿宝外卖通知", "点击查看详细", "huawei", "message", "go_custom", $pushData);
    //小米测试
    $result = $push->send("1433346", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", "message", "go_custom", $pushData);
    print_r($result);
} catch (Exception $e) {
    print_r($e);
}