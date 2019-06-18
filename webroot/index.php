<?php
use Mofing\DoNewsPush\Push;

require dirname(__DIR__) . '/config/paths.php';
require dirname(__DIR__) . '/vendor/autoload.php';

// 读取环境变量
if (file_exists(CONFIG . '.env')) {
    $dotenv = new \josegonzalez\Dotenv\Loader([
        CONFIG . '.env'
    ]);
    $dotenv->parse()
    ->putenv()
    ->toEnv()
    ->toServer();
}
try {
    // 测试发送
    $push = new Push();
    /* $result = $push->send("0862848040691494300003689500CN01", "阿宝天气提醒".time(), "推送的消息内容,点击查看详情", "huawei", "message", "go_custom", [
        ["type" => "typetypetype"],
        ["id" => "this is id"]
    ]); */
    
    $data = [
        'assort'=>8,
        'mtype'=>2,
        'title'=>'您的外卖订单,商家已经确认接单',
        'role'=>3,// 1:店主 2:骑手 3:买家
        'shop_id'=>2536184,
        'order_id'=>123456,
        'status' =>1,
        'sound' =>1,
    ];  
    $pushData =[
        'id'=> '',
        'type'=>$data['assort'],  // 信息大分类
        'mtype'=>$data['mtype'],  // 信息小分类
        'title'=>$data['title'],  // 标题
        'role'=>$data['role'],    // 1:店主 2:骑手 3:买家
        'sound' =>1,
        'data'=>[
            'shop_id' => $data['shop_id'],  // 店铺ID
            'order_id'=> $data['order_id'], // 订单ID
            'status'=> $data['status'],     // 状态ID
            'uid'=>$uid,
        ]
    ];
    $result=[];
    //华为测试
    //$result = $push->send("0862848040691494300003689500CN01", "阿宝外卖通知", "点击查看详细", "huawei", "message", "go_app", $pushData);
    //$result = $push->send("0860916034765707300003689500CN01", "阿宝外卖通知", "点击查看详细", "huawei", "message", ["go_app","com.mofing/mainActivity"], $pushData);
    //$result = $push->send("0860916034765707300003689500CN01", "阿宝外卖通知", "点击查看详细", "huawei", "message", ["go_custom",""], $pushData);
    //$result = $push->send("0862848040691494300003689500CN01", "阿宝外卖通知", "点击查看详细", "huawei", "message", "go_scheme", $pushData);
    //小米测试
    //$result = $push->send("1433346", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", "quiet", "go_custom", $pushData);
    //透传测试ok
    //$result = $push->send("1580855", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", "quiet", "go_custom", $pushData);
    //$result = $push->send("1433346", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", "message", "go_custom", $pushData);
    //$result = $push->send("1433346", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", "message", "go_custom", $pushData);
    //$result = $push->send("9115", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", "message", ["go_custom","abao://push?"], $pushData);
    //$result = $push->send("1433346", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", "message", ["go_custom","abao://push?"], $pushData);
    //vivo测试
    //$result = $push->send("1458318", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "vivo", "message", ["go_page",["abc"=>"2333"]], ["push"=>$pushData]);
    //oppo测试
    //$result = $push->send("CN_1922ffcc67487d7ffd64708515f6d38f", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "oppo", "message", ["go_page","com.abao.oppopush"], $pushData);
    
    //组合推送
    //$result =$push->unionSend(1433346, "CN_7c966fae247d75695ff6c26102f1f80d", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "oppo", $pushData);
    //消息推送+点击通知栏，跳转到指定界面
    //$result[] =$push->sendWithClick("1433346", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", $pushData);
    //$result[] =$push->sendWithClick("0862848040691494300003689500CN01", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "huawei", $pushData);
    //$result[] =$push->sendWithClick("99", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "vivo", $pushData);
    $result[] =$push->sendWithClick("Realme_CN_028a137970d28c0c1721c87331644bf3", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "oppo", $pushData);
    print_r($result);
} catch (Exception $e) {
    print_r($e);
}