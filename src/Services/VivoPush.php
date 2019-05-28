<?php
namespace Mofing\DoNewsPush\Services;

/**
 * vivo系统通知,只有通知栏通知，点击之后，才能传递到自己的app
 * 
 * @author Wenchaojun <343169893@qq.com>
 * @link https://swsdl.vivo.com.cn/appstore/developer/uploadfile/20190418/0d23g6/PUSH-UPS-API%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A3%20-%202.4.3.1%E7%89%88.pdf
 */
class VivoPush extends BasePush
{

    /**
     * 授权获得accesstoken的地址
     *
     * @var string
     */
    var $_authUrl = "https://api-push.vivo.com.cn/message/auth";

    /**
     * 发送消息的地址
     *
     * @var string
     */
    var $_sendUrl = "https://api-push.vivo.com.cn/message/send";

    var $_authCacheKey = "vivo_push_authtoken";
    
    var $_httpHeaderContentType="application/json";
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
     * 点击跳转类型 1：打开 APP 首页 2：打开链接 3：自定义 4:打开 app 内指定页面
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::getAfterOpen()
     */
    public function getAfterOpen($go_after)
    {
        list ($type, $param) = $go_after;
        if ($type == "go_custom") {
            return [
                'skipType' => 3,
                'skipContent' => json_encode($param)
            ];
        } elseif ($type == "go_page") {
            return [
                'skipType' => 4,
                'skipContent' => json_encode($param)
            ];
        } elseif ($type == "go_url") {
            // Action的type为2的时候表示打开URL地址,跳转内容最大1000 个字符
            return [
                'skipType' => 2,
                'skipContent' => $param
            ];
        }
        // 需要拉起的应用包名，必须和注册推送的包名一致。
        return [
            'skipType' => 1,
            'skipContent' => $this->pkgName
        ];
    }

    /**
     * 发送vivo推送消息。
     * 
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Services\BasePush::sendMessage()
     */
    public function sendMessage($deviceToken, $title, $message, $type, $after_open, $customize)
    {
        $data = [
            // 用户ID
            "title" => $title,
            "content" => $message,
            "timeToLive" => 96400,
            "notifyType" => 4, // 通知类型 1:无，2:响铃，3:振动，4:响铃和振动
                                 // 自定义参数
            "clientCustomMap" => $customize
        ];
        // 合并发送方式
        $data = array_merge($data, $this->getHttpSendType($deviceToken));
        // 点击跳转类型
        $data = array_merge($data, $this->getAfterOpen($after_open));
        // 用户请求唯一标识 最大 64 字符
        $data["requestId"] = md5(json_encode($data) . time());
        
        // 获取token
        $accessToken = $this->getAccessToken();
        $response = $this->_http->post($this->_sendUrl, [
            'headers' => [
                "Content-Type" => "application/json",
                "authToken" => $accessToken
            ],
            'data' => json_encode($data)
        ]);
        $this->result = $response->getResponseArray();
        // 接口调用是否成功的状态码 0 成功，非 0 失败
        if (! empty($this->result) && empty($this->result["result"])) {
            return $this->result["taskId"];
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
        if ($this->httpSendType == "alias") {
            return [
                "alias" => $deviceToken
            ];
        } else {
            return [
                "regId" => $deviceToken
            ];
        }
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Mofing\DoNewsPush\Services\BasePush::getAuthData()
     */
    protected function getAuthData()
    {
        $t = $this->getTime();
        $data = [
            "appId" => $this->appId,
            "appKey" => $this->appKey,
            "timestamp" => $t
        ];
        $sign = md5(implode("", $data) . $this->appSecret);
        $data["sign"] = $sign;
        return json_encode($data);
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Mofing\DoNewsPush\Services\BasePush::getResponseToken()
     */
    protected function getResponseToken($data)
    {
        // 接口调用是否成功的状态码 0 成功，非 0 失败
        if (! empty($data["result"])) {
            return false;
        }
        // 当鉴权成功时才会有该字段，推送消息时，需要提供authToken，有效期默认为 1 天，过期后无法使用
        return $data["authToken"];
    }

}