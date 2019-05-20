<?php
namespace Mofing\DoNewsPush;

use Redis;
use Mofing\DoNewsPush\Exceptions\PushException;
use Mofing\DoNewsPush\Contracts\DoNewsPusher;

class Push implements DoNewsPusher
{

    private static $_config = null;

    /**
     *
     * @var \Redis
     */
    private $_redis = null;

    public function __construct()
    {
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
        // 得到相关配置
        $config = require_once dirname(__DIR__) . "/config/push.php";
        static::$_config = $config;
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
            case 'umeng':
                $service = "UmengPush";
                break;
            case 'vivo':
                $service = "VivoPush";
                break;
            case 'oppo':
                $service = "OppoPush";
                break;
            case 'meizu':
                $service = "MeizuPush";
                break;
            default:
                // 默认走友盟
                $service = "UmengPush";
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
        // 得到相关的类名称
        $service = $this->getSerivce($platform);
        $config = static::$_config["platform"][$platform];
        // 获得redis配置，有些不需要
        $config["redis"] = static::$_config["redis"]["default"];
        /**
         *
         * @var \Mofing\DoNewsPush\Services\BasePush $push
         */
        $push = new $service($config);
        $push->setPkgName(static::$_config["pkgname"]);
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
     * 根据用户ID设置用户token
     */
    public function setToken($platform, $app_id, $user_id, $deviceToken)
    {
        if (! $app_id || ! $user_id || ! $deviceToken || ! $platform) {
            return false;
        }
        $this->_redis->set($app_id . ":" . $user_id, $platform . ":" . $deviceToken);
        return true;
    }

    /**
     * 根据用户ID获取用户token
     */
    public function getToken($app_id, $user_id)
    {
        return $this->_redis->get($app_id . ":" . $user_id);
    }

    /**
     * 根据用户ID设置用户token
     */
    public function setDeviceToken($app_id, $list_name, $platform, $deviceToken)
    {
        return $this->_redis->lpush($app_id . $list_name, $platform . ':' . $deviceToken);
    }

    /**
     * 根据用户ID设置用户token
     */
    public function getDeviceToken($app_id, $list_name, $page = 1, $pageSize = 100)
    {
        return $this->_redis->lrange($app_id . $list_name, ($page - 1) * $pageSize, $pageSize);
    }

    // 返回列表长度
    public function getListLen($app_id, $list_name)
    {
        return $this->_redis->llen($app_id . $list_name);
    }

    public function success()
    {
        throw new PushException("success", 200);
    }

    public function error()
    {
        throw new PushException("参数错误", 405);
    }
}