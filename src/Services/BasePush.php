<?php
namespace Mofing\DoNewsPush\Services;

use Redis;
use Mofing\DoNewsPush\Contracts\PushInterface;
use Singiu\Http\Request;
use Singiu\Http\Http;

class BasePush implements PushInterface
{

    /**
     * 包名
     *
     * @var string
     */
    var $pkgName = "";

    /**
     * redis控制类
     *
     * @var \Redis
     */
    var $_redis;

    var $appId;

    var $appKey;

    var $appSecret;

    /**
     * 自定义跳转的active
     *
     * @var string
     */
    var $intentUri;

    /**
     * 开发环境sandbox/production
     *
     * @var unknown
     */
    var $appEnvironment;

    var $_http;

    /**
     * 授权获得accesstoken的地址
     *
     * @var string
     */
    var $_authUrl;

    /**
     * token缓存key
     * 
     * @var string
     */
    var $_authCacheKey = "push";

    /**
     * 发送消息的地址
     *
     * @var string
     */
    var $_sendUrl;

    /**
     * 不同的发送消息类型
     * 
     * @var string
     */
    var $httpSendType;

    var $result;
    
    var $customize;

    protected $_redisConfig = [
        'database' => 0,
        'duration' => 3600,
        'groups' => [],
        'password' => false,
        'persistent' => true,
        'port' => 6379,
        'prefix' => 'php_',
        'probability' => 100,
        'host' => '127.0.0.1',
        'timeout' => 0,
        'unix_socket' => false
    ];

    /**
     * 构造函数。
     *
     * @param array $config            
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        foreach ($config as $k => $v) {
            if ($k == "redis") {
                $this->_redisConfig = array_merge($this->_redisConfig, $v);
            } else {
                $this->{$k} = $v;
            }
        }
        $this->_http = new Request();
        $this->_http->setHttpVersion(Http::HTTP_VERSION_1_1);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::sendMessage()
     */
    public function sendMessage($deviceToken, $title, $message, $type, $after_open, $customize)
    {
        // TODO Auto-generated method stub
    }

    var $_httpHeaderContentType="application/x-www-form-urlencoded";
    /**
     * 请求新的 Access Token。
     *
     * @param number $tryCount
     *            可重试次数
     * @throws \Exception
     * @return string
     */
    protected function getAccessToken($tryCount = 1)
    {
        $key = $this->getCacheKey($this->_authCacheKey);
        $accessToken = $this->_redis->get($key);
        if (! $accessToken) {
            $data = $this->getAuthData();
            // 有很大几率会调用失败
            $result = $this->_http->post($this->_authUrl, [
                'data' => $data,
                'headers' => [
                    "Content-Type" => $this->_httpHeaderContentType
                ]
            ])->getResponseArray();
            $accessToken = $this->getResponseToken($result);
            if (empty($accessToken)) {
                // 获取token失效
                if ($tryCount < 1) {
                    throw new \Exception("获取token失败");
                }
                // 过一会儿重试
                sleep(1);
                return $this->getAccessToken($tryCount - 1);
            }
            // 设置的缓存小于实际100秒，有利于掌控有效期,默认缓存半小时,每天获取的机会还是很多的
            $this->_redis->setex($key, 1800, $accessToken);
        }
        return $accessToken;
    }

    /**
     * 获得鉴权请求post参数
     * 
     * @return array
     */
    protected function getAuthData()
    {
        return [];
    }

    /**
     * 获取鉴权之后的token
     * 
     * @param [] $data            
     * @return boolean
     */
    protected function getResponseToken($data)
    {
        return false;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::getSendType()
     */
    public function getSendType($type)
    {
        // TODO Auto-generated method stub
        return 1;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \Mofing\DoNewsPush\Contracts\PushInterface::getAfterOpen()
     */
    public function getAfterOpen($go_after)
    {
        // TODO Auto-generated method stub
        return [];
    }

    /**
     * 连接redis缓存
     *
     * @return boolean
     */
    protected function getRedisConnection()
    {
        try {
            $this->_redis = new Redis();
            if (! empty($this->_redisConfig['unix_socket'])) {
                $return = $this->_redis->connect($this->_redisConfig['unix_socket']);
            } elseif (empty($this->_redisConfig['persistent'])) {
                $return = $this->_redis->connect($this->_redisConfig['server'], $this->_redisConfig['port'], $this->_redisConfig['timeout']);
            } else {
                $persistentId = $this->_redisConfig['port'] . $this->_redisConfig['timeout'] . $this->_redisConfig['database'];
                $return = $this->_redis->pconnect($this->_redisConfig['host'], $this->_redisConfig['port'], $this->_redisConfig['timeout'], $persistentId);
            }
        } catch (\Exception $e) {
            return false;
        }
        if ($return && $this->_redisConfig['password']) {
            $return = $this->_redis->auth($this->_redisConfig['password']);
        }
        if ($return) {
            $return = $this->_redis->select($this->_redisConfig['database']);
        }
        return $return;
    }

    /**
     * 重构缓存key
     *
     * @param string $key            
     * @return string
     */
    public function getCacheKey($key)
    {
        if (! empty($this->_redisConfig["prefix"])) {
            return $this->_redisConfig["prefix"] . $key;
        }
        return $key;
    }

    /**
     * 根据发送类型，返回对应的字段
     * 
     * @param string $deviceToken            
     */
    public function getHttpSendType($deviceToken)
    {
        return [];
    }

    public function setPkgName($name)
    {
        $this->pkgName = $name;
    }

    public function getPkgName()
    {
        return $this->pkgName;
    }

    public function getResult()
    {
        return $this->result;
    }
    /**
     * 获取毫秒时间戳
     *
     * @return number
     */
    protected function getTime()
    {
        list ($msec, $sec) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }
}

