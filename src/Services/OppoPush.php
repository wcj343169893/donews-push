<?php
namespace Mofing\DoNewsPush\Services;

class OppoPush extends BasePush
{

    var $masterSecret = "";

    var $_authUrl = "https://api.push.oppomobile.com/server/v1/auth";

    var $_sendUrl = "https://api.push.oppomobile.com/server/v1/message/notification/unicast";

    var $_authCacheKey = "oppo_push_authtoken";

    /**
     * 构造函数。
     *
     * @param array $config            
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        // 连接redis服务器，用来存储accessToken
        $this->getRedisConnection();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::sendMessage()
     */
    public function sendMessage($deviceToken, $title, $message, $type, $after_open, $customize)
    {
        $accessToken = $this->getAccessToken();
        
        $notification = [
            'title' => $title,
            'content' => $message,
            'sub_title' => ""
        ];
        //动作参数，打开应用内页或网页时传递
       /*  $notification['action_parameters'] = json_encode([
            'type' => $type,
            'id' => $id
        ]); */
        //点击动作类型 0，启动应用；1，打开应 用内页（activity 的 intent action）；2， 打开网页；4，打开应用内页（activity）； 【非必填，默认值为 0】;5,Intent scheme URL
        $notification=array_merge($notification,$this->getAfterOpen($after_open));
        //动作参数，打开应用内页或网页时传递 给应用或网页【JSON 格式，非必填】，        字符数不能超过 4K，示例：        {"key1":"value1","key2":"value2"}
        $notification["action_parameters"]=["push"=>json_encode($customize,JSON_UNESCAPED_UNICODE)];
        // 合并目标类型
        $msg = $this->getHttpSendType($deviceToken);
        $msg["notification"] = $notification;
        
        $data['auth_token'] = $accessToken;
        $data['message'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
       
        $response = $this->_http->post($this->_sendUrl, [
            'headers' => [
                "Content-Type" => "application/x-www-form-urlencoded"
            ],
            'data' => $data
        ]);
        $this->result = $response->getResponseArray();
        if(!empty($this->result) && empty($this->result["code"])){
            return $this->result["data"]["messageId"];
        }else{
            print_r($response);
        }
        return false;
    }
    /**
     * 点击动作类型 
     * 0，启动应用；
     * 1，打开应 用内页（activity 的 intent action）；
     * 2， 打开网页；
     * 4，打开应用内页（activity）； 【非必填，默认值为 0】;
     * 5,Intent scheme URL
     * {@inheritDoc}
     * @see \Mofing\DoNewsPush\Services\BasePush::getAfterOpen()
     */
    public function getAfterOpen($go_after){
        //click_action_type
        //click_action_activity
        //click_action_url
        list ($type, $param) = $go_after;
        if ($type == "go_page") {
            //指定应用内页
            return [
                'click_action_type' =>1,
                //1 时这里填写 com.coloros.push.demo.internal
                //4 时这里填写com.coloros.push.demo.component.InternalActivity
                'click_action_activity' =>$param,
            ];
        } elseif ($type == "go_custom") {
            return [
                'click_action_type' =>4,
                //com.abao.oppopush
                'click_action_url' =>"com.abao.oppopush"
            ];
        } elseif ($type == "go_scheme") {
            return [
                'click_action_type' =>5,
                //abao://push?或者intent:#Intent;action=com.a.b.shot;end
                'click_action_url' =>$param// sprintf('intent:#Intent;component=%s;end', empty($param) ? $this->intentUri : $param)
            ];
        } elseif ($type == "go_url") {
            return [
                'click_action_type' => 2,
                'click_action_url' => $param
            ];
        }
        //go_app
        return [
            'click_action_type' => 0
        ];
    }

    /**
     * 目标类型 2:registration_id ;3: 别名推送alias_name;
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Services\BasePush::getHttpSendType()
     */
    public function getHttpSendType($deviceToken)
    {
        if ($this->httpSendType == "alias") {
            //别名推送,暂时不支持
            return [
                "target_type" => 3,
                "target_value" => $deviceToken
            ];
        }
        return [
            "target_type" => 2,
            "target_value" => $deviceToken
        ];
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Services\BasePush::getResponseToken()
     */
    protected function getResponseToken($data)
    {
        // 返回码,请参考平台返回码与接口返回码,0 Success 成功，只表明接口调用成功
        if (! empty($data["code"])) {
            return false;
        }
        // 当鉴权成功时才会有该字段，推送消息时，需要提供authToken，有效期默认为 1 天，过期后无法使用
        return $data["data"]["auth_token"];
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Services\BasePush::getAuthData()
     */
    protected function getAuthData()
    {
        $t = $this->getTime();
        $data = [
            "app_key" => $this->appKey,
            "timestamp" => $t
        ];
        $sign = hash("sha256", implode("", array_values($data)) . $this->masterSecret);
        $data["sign"] = $sign;
        return $data;
    }
}