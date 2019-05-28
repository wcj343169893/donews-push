<?php
namespace Mofing\DoNewsPush\Services;

use Singiu\Http\Response;
use Mofing\DoNewsPush\Contracts\PushInterface;

/**
 * 华为推送
 * 1.通知栏通知，在app未启动条件下，能收到通知，但是不能传递给app
 * 2.透传消息,在app清除情况下，无法收到通知和内容
 *
 * @author Wenchaojun <343169893@qq.com>
 * @link https://developer.huawei.com/consumer/cn/service/hms/catalog/huaweipush_agent.html?page=hmssdk_huaweipush_api_reference_agent_s2
 */
class HmsPush extends BasePush
{

    /**
     * 获取token地址
     *
     * @var string
     */
    var $_authUrl = "https://login.cloud.huawei.com/oauth2/v2/token";

    /**
     * 发送推送地址
     *
     * @var string
     */
    var $_sendUrl = "https://api.push.hicloud.com/pushsend.do";
    
    var $_authCacheKey="huawei_push_authtoken";
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
     * 发送华为推送消息。
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::sendMessage()
     */
    public function sendMessage($deviceToken, $title, $message, $type, $after_open, $customize)
    {
        date_default_timezone_set('PRC'); // 设置中国时区
        $time = time();
        // 构建 Payload
        $this->customize = $customize;
        if (is_array($message)) {
            $payload = json_encode($message, JSON_UNESCAPED_UNICODE);
        } else if (is_string($message)) {
            $payload = json_encode([
                'hps' => [
                    'msg' => [
                        'type' => $this->getSendType($type), // 1 透传异步消息 3 系统通知栏异
                        'body' => [
                            'title' => $title,
                            'content' => $message
                        ],
                        'action' => $this->getAfterOpen($after_open)
                    ],
                    'ext' => [
                        'customize' => [
                            $customize
                        ]
                    ]
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $payload = '';
        }
        // 获取token
        $accessToken = $this->getAccessToken();
        if (! is_array($deviceToken)) {
            $deviceToken = [
                $deviceToken
            ];
        }
        // echo $payload;
        // 发送消息通知
        $response = $this->_http->post($this->_sendUrl, [
            'query' => [
                'nsp_ctx' => json_encode([
                    'ver' => '1',
                    'appId' => $this->appId
                ])
            ],
            'headers' => [
                "Content-Type" => "application/x-www-form-urlencoded"
            ],
            'data' => [
                'access_token' => $accessToken,
                'nsp_svc' => 'openpush.message.api.send',
                'nsp_ts' => $time, // 服务请求时间戳
                'device_token_list' => json_encode($deviceToken),
                'payload' => $payload,
                'expire_time' => date("Y-m-d\TH:i", strtotime("+3 days"))
            ]
        ]);
        $this->result = $response->getResponseArray();
        if(!empty($this->result) && $this->result["code"]=="80000000"){
            return $this->result["requestId"];
        }
        return false;
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
        // 1 透传异步消息 3 系统通知栏异
        $msgArr = [
            "message" => 3,
            "quiet" => 1
        ];
        return $msgArr[$type];
    }

    /**
     * 点击之后的打开行为
     * 1 自定义行为：行为由参数intent定义 ,2 打开URL：URL地址由参数url定义, 3 打开APP：默认值，打开App的首页
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
                'type' => 1,
                'param' => [
                    // 'intent' => '#Intent;compo=com.wanmei.a9vg/.common.activitys.Activity;S.W=U;end'
                    'intent' => sprintf('#Intent;compo=%s;S.W=U;end', empty($param) ? $this->intentUri : $param)
                ]
            ];
        } elseif ($type == "go_scheme") {
            return [
                'type' => 1,
                'param' => [
                    //abao://router/huawei
                    'intent' =>sprintf("%s?push=%s",$this->intentUri,urlencode(json_encode($this->customize,JSON_UNESCAPED_UNICODE)))
                ]
            ];
        } elseif ($type == "go_url") {
            // Action的type为2的时候表示打开URL地址
            return [
                'type' => 2,
                'param' => [
                    "url" => $param
                ]
            ];
        }
        // 需要拉起的应用包名，必须和注册推送的包名一致。
        return [
            'type' => 3,
            'param' => [
                'appPkgName' => $this->pkgName
            ]
        ];
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Mofing\DoNewsPush\Services\BasePush::getAuthData()
     */
    protected function getAuthData(){
        return [
            'grant_type' => 'client_credentials',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret
        ];
    }
    /**
     * 获取鉴权之后的token
     * {@inheritDoc}
     * @see \Mofing\DoNewsPush\Services\BasePush::getResponseToken()
     */
    protected function getResponseToken($data){
        if(!isset($data['access_token'])){
            return false;
        }
        return $data['access_token'];
    }
}