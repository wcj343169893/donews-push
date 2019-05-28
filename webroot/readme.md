### oppo
oppo打开指定action， 指定action-> com.abao.oppopush    插入键值对 push="{"id":"","type":8,"mtype":2,"title":"您的外卖订单,商家已经确认接单","role":3,"sound":1,"data":{"shop_id":2536184,"order_id":123456,"status":1,"uid":null}}"
```
$result = $push->send("CN_acc4de3a3f5c202cdd50e5f8600abba8", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "oppo", "message", ["go_page","com.abao.oppopush"], $pushData);
```
接收到数据android 处理逻辑  在OPPOPushMessageActivity 类的onCreate方法通过hm.get("push") 去获取服务器推送过来的数据，oppo 高版本必须在初始化oppo推送的onRegister 回调里面传递token给服务器

### 华为推送
华为推送scheme类型推送，  abao://router/huawei?push={"id":"","type":8,"mtype":2,"title":"您的外卖订单,商家已经确认接单","role":3,"sound":1,"data":{"shop_id":2536184,"order_id":123456,"status":1,"uid":null}}
```
$result = $push->send("0862848040691494300003689500CN01", "阿宝外卖通知", "点击查看详细", "huawei", "message", "go_scheme", $pushData);
```
华为上传token给服务器在HuaweiPushReceiver 类的onToken方法调用，后台推送过来的数据通过url跟参数的形式获取，既HuaweiPushMessageActivity的onCreate方法的uri.getQueryParameter("push")获取到数据并处理，


### 小米推送
小米推送 设置用户自定义，并传入对应的数据
```
$result = $push->send("1433346", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "xiaomi", "message", "go_custom", $pushData);
```
小米通过MiPushReceiver类的onNotificationMessageClicked去处理推送过来的数据点击事件，服务器要设置打开方式为用户自定义

### vivo
vivo 直接传对应数据，是个集合，
```
$result = $push->send("1458318", "阿宝外卖通知", "点击查看详细点击查看详细点击查看详细", "vivo", "message", ["go_page",["abc"=>"2333"]], ["push"=>$pushData]);
```
vivo通过VivoPushReceiver类的onNotificationMessageClicked方法，通过循环数组去获取推送过来的数据，由于需求原因取数组的第一个角标，服务器也只推一条过来

其余手机通过友盟推送，如果应用没启动首先启动，如果应用启动了直接给QT处理以上四种都是