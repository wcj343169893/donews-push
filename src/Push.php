<?php
namespace Mofing\DoNewsPush;

use Mofing\DoNewsPush\Contracts\DoNewsPusher;

class Push implements DoNewsPusher
{
    private $_config = null;
    private static $config=null;
    
    public function __construct()
    {
        // 得到相关配置
        if(empty(self::$config)){
            self::$config = include dirname(__DIR__) . "/config/push.php";
        }
        $this->_config = self::$config;
    }

    /**
     * 获取推送平台
     *
     * @param string $platform            
     * @return string
     */
    private function getSerivce($platform)
    {
        switch ($platform) {
            case 'apple':
                $service = "ApnsPush";
                break;
            case 'xiaomi':
                $service = "XiaomiPush";
                break;
            case 'huawei':
                $service = "HmsPush";
                break;
            case 'vivo':
                $service = "VivoPush";
                break;
            case 'oppo':
                $service = "OppoPush";
                break;
            default:
                $service = "";
                return false;
                break;
        }
        return "Mofing\\DoNewsPush\\Services\\" . $service;
    }

    /**
     * 统一推送接口。
     *
     * @param
     *            $deviceToken
     * @param
     *            $title
     * @param
     *            $message
     * @param string $platform
     *            平台名称apple mi huawei umeng vivo meizu
     * @param string $after_open
     *            go_custom/go_app/go_url
     * @return mixed
     */
    public function send($deviceToken, $title, $message, $platform, $type, $after_open, $customize)
    {
        $platform=strtolower($platform);
        // 得到相关的类名称
        $service = $this->getSerivce($platform);
        if(empty($service)){
            return false;
        }
        $config = $this->_config["platform"][$platform];
        // 获得redis配置，有些不需要
        $config["redis"] = $this->_config["redis"]["default"];
        /**
         *
         * @var \Mofing\DoNewsPush\Services\BasePush $push
         */
        $push = new $service($config);
        $push->setPkgName($this->_config["pkgname"]);
        if (! is_array($after_open)) {
            // 也许存在多个参数
            $after_open = [
                $after_open,
                "",
                ""
            ];
        }
        return $push->sendMessage($deviceToken, $title, $message, $type, $after_open, $customize);
    }
    /**
     * 消息栏提示，点击消息栏，跳转到指定页面
     * @param string $deviceToken
     * @param string $title
     * @param string $message
     * @param string $platform
     * @param [] $customize
     * @return boolean
     */
    public function sendWithClick($deviceToken, $title, $message, $platform, $customize){
        $platform=strtolower($platform);
        //获得平台的配置
        if(empty($this->_config["platform"][$platform])){
            //不支持的类型
            return false;
        }
        $config = $this->_config["platform"][$platform];
        if($platform=="huawei"){
            return $this->send($deviceToken, $title, $message, $platform, "message", "go_scheme", $customize);
        }elseif ($platform=="oppo"){
            return $this->send($deviceToken,$title, $message, $platform, "message", ["go_page",$config["intentUri"]], $customize);
        }elseif ($platform=="xiaomi"){
            return $this->send($deviceToken, $title, $message, $platform, "message", "go_custom", $customize);
        }elseif ($platform=="vivo"){
            return $this->send($deviceToken, $title, $message, $platform, "message", ["go_page",["client"=>"donews-push"]], ["push"=>$customize]);
        }else{
            return false;
        }
    }
    
    /**
     * 联合推送,最后一次登录设备+小米透传，如果是小米手机，直接使用通知
     * @param int $uid
     * @param string $deviceToken
     * @param string $title
     * @param string $message
     * @param string $platform
     * @param [] $customize
     */
    public function unionSend($uid,$deviceToken, $title, $message, $platform, $customize)
    {
        $result = [];
        if($platform=="xiaomi"){
            $result = $this->send($deviceToken, $title, $message, "xiaomi", "message", "go_custom", $customize);
        }else{
            $result []= $this->send($uid, $title, $message, "xiaomi", "quiet", "go_custom", $customize);
            $result []=$this->send($deviceToken, $title, $message, $platform, "message", "go_custom", $customize);
        }
        return $result;
    }
    /**
     * 定时刷新token
     * @param string $platform
     * @return boolean|string
     */
    public function refreshToken($platform){
        $platform=strtolower($platform);
        // 得到相关的类名称
        $service = $this->getSerivce($platform);
        if(empty($service)){
            return false;
        }
        $config = $this->_config["platform"][$platform];
        // 获得redis配置，有些不需要
        $config["redis"] = $this->_config["redis"]["default"];
        /**
         *
         * @var \Mofing\DoNewsPush\Services\BasePush $push
         */
        $push = new $service($config);
        $push->setPkgName($this->_config["pkgname"]);
        try {
            return $push->getAccessToken(1,true);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}