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
     * 发送消息的地址
     *
     * @var string
     */
    var $_sendUrl;
    
    /**
     * 不同的发送消息类型
     * @var string
     */
    var $httpSendType;

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

    public function setPkgName($name)
    {
        $this->pkgName = $name;
    }

    public function getPkgName()
    {
        return $this->pkgName;
    }
}

