<?php
namespace Mofing\DoNewsPush\Services;

/**
 * 小米推送类
 * 1.通知栏通知，在app未启动条件下，能收到通知，但是不能传递给app
 * 2.透传消息,在app清除情况下，无法收到通知和内容
 *
 * @author Wenchaojun <343169893@qq.com>
 * @link https://dev.mi.com/console/doc/detail?pId=1163
 */
class XiaomiPush extends BasePush
{

    // 向某个regid或一组regid列表推送某条消息（这些regId可以属于不同的包名）
    // private $_sendUrl = 'https://api.xmpush.xiaomi.com/v3/message/regid';
    // 向某个alias或一组alias列表推送某条消息（这些alias可以属于不同的包名）
    private $_sendBaseUrl = 'https://api.xmpush.xiaomi.com/v3';

    private $_intent_uri;

    /**
     * 发送推送通知
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::sendMessage()
     */
    public function sendMessage($deviceToken, $title, $message, $type, $after_open, $customize)
    {
        $payload = [
            'title' => $title, // 通知栏展示的通知的标题，这里统一不显示。
            'description' => $message,
            'pass_through' => $this->getSendType($type), // 设定是否为透传消息，0 = 推送消息，1 = 透传消息。
            'payload' => urlencode(json_encode($customize, JSON_UNESCAPED_UNICODE)), // 消息内容。需要对payload字符串做urlencode处理
            'notify_type' => - 1, // 提示通知默认设定，-1 = DEFAULT_ALL。
            'restricted_package_name' => $this->pkgName,
            
            'extra.ticker' => $title, // 开启通知消息在状态栏滚动显示。
            'extra.notify_foreground' => 1,
            // extra.sound_uri=android.resource://com.xiaomi.mipushdemo/raw/shaking
            'extra.sound_url' => "default"
        ];
        // 合并发送方式
        $payload = array_merge($payload, $this->getHttpSendType($deviceToken));
        // 合并点击后打开方式
        $payload = array_merge($payload, $this->getAfterOpen($after_open));
        $response = $this->_http->post($this->_sendUrl, [
            'headers' => [
                'Authorization' => 'key=' . $this->appSecret
            ],
            'data' => $payload
        ]);
        $this->result =  $response->getResponseArray();
        //"code": integer，0表示成功，非0表示失败。
        if(!empty($this->result) && empty($this->result["code"])){
            return $this->result["trace_id"];
        }
        return false;
    }

    /**
     * 获得发送方式和地址
     *
     * @param string $deviceToken            
     * @return []
     */
    public function getHttpSendType($deviceToken)
    {
        if (! is_array($deviceToken)) {
            $deviceToken = [
                $deviceToken
            ];
        }
        $glue = ",";
        $types = [
            "regid" => [
                "url" => "/message/regid",
                "key" => "registration_id"
            ],
            "alias" => [
                "url" => "/message/alias",
                "key" => "alias"
            ],
            "user_account" => [
                "url" => "/message/topic",
                "key" => "user_account"
            ],
            "topic" => [
                "url" => "/message/topic",
                "key" => "topic"
            ],
            "topics" => [
                "url" => "/message/multi_topic",
                "key" => "topics"
            ]
        ];
        $this->_sendUrl = $this->_sendBaseUrl . $types[$this->httpSendType]["url"];
        if ($this->httpSendType == "topics") {
            // topic之间的操作关系。支持以下三种：
            // UNION并集
            // INTERSECTION交集
            // EXCEPT差集
            return [
                "topics" => implode(";$;", $deviceToken),
                "topic_op" => "UNION"
            ];
        }
        return [
            $types[$this->httpSendType]["key"] => implode($glue, $deviceToken)
        ];
    }

    /**
     * 点击后打开方式
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::getAfterOpen()
     */
    public function getAfterOpen($go_after)
    {
        // 预定义通知栏消息的点击行为，
        //“1″：通知栏点击后打开app的Launcher Activity。
        //“2″：通知栏点击后打开app的任一Activity（开发者还需要传入extra.intent_uri）。
        //“3″：通知栏点击后打开网页（开发者还需要传入extra.web_uri）。
        list ($type, $param) = $go_after;
        if ($type == "go_custom") {
            //相当于小米测试页面的由应用客户端自定义 ：0
           return [];
        } elseif ($type == "go_page") {
            return [
                'extra.notify_effect' =>2,
                //abao://push?或者intent:#Intent;action=com.a.b.shot;end
                'extra.intent_uri' =>$param// sprintf('intent:#Intent;component=%s;end', empty($param) ? $this->intentUri : $param)
            ];
        } elseif ($type == "go_url") {
            // Action的type为2的时候表示打开URL地址
            return [
                'extra.notify_effect' => 3,
                'extra.web_uri' => $param
            ];
        }
        // 需要拉起的应用包名，必须和注册推送的包名一致。
        return [
            'extra.notify_effect' => 1,
            'extra.intent_uri' =>""
        ];
    }

    /**
     * 转换透传和消息
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::getSendType()
     */
    public function getSendType($type)
    {
        // 1 透传异步消息; 0推送消息
        $msgArr = [
            "message" => 0,
            "quiet" => 1
        ];
        return $msgArr[$type];
    }
}