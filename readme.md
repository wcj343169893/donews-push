# DoNews APP推送 现在仅支持:华为，oppo，小米，vivo
## 安装
```
composer require mofing/donews-push
```

## 配置
- 本拓展包中使用 redis 保存 客户端Token 
- 在自己的项目中设置全局变量CONFIG,用来存放.env全局变量

```
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
//具体按实际路径配置
if (!defined('DS')) {
	define('CONFIG', dirname(__DIR__) . DS . 'config' . DS);
}
```

.env配置文件设置
```
APP_PKG_NAME="com.xxx"
# redis
REDIS_HOST = "192.168.99.100"
REDIS_PORT = 6379
REDIS_PREFIX = "red_"
REDIS_DURATION = 3600
REDIS_DATABASE = 2

# push message
## huawei push
HUAWEI_CLIENT_ID=""
HUAWEI_CLIENT_SECRET=""
HUAWEI_APP_INTENT=""

## xiaomi push
XIAOMI_APP_SECRET=""
XIAOMI_APP_INTENT_URI=""
XIAOMI_APP_SEND_TYPE="alias"

## apple push
APNS_CERTIFICATE_PATH=""
APNS_CERTIFICATE_PASSPHRASE=""
APNS_ENVIRONMENT="sandbox"

## vivo push
VIVO_APP_ID=""
VIVO_APP_KEY=""
VIVO_APP_SECRET=""

## oppo push
OPPO_APP_KEY=""
OPPO_MASTER_SECRET=""
OPPO_MASTER_SECRET=""
OPPO_APP_SEND_TYPE="registration"
OPPO_APP_INTENT_URI="com.oppopush"
```

## 独立测试使用nginx+php5.6
1.创建网站,配置访问
```
server
    {
        listen 80;
        server_name push.mofing.com ;
        index index.php;
        root  /home/wwwroot/www.push.com/webroot;

		location ~ [^/]\.php(/|$)
        {
            try_files $uri =404;
            fastcgi_pass  unix:/tmp/php-cgi.sock;
            fastcgi_index index.php;
            include fastcgi.conf;
            fastcgi_param PHP_ADMIN_VALUE "open_basedir=/home/wwwroot/www.push.com/:/tmp/:/proc/";
        }

        access_log  /home/wwwlogs/www.push.com.log;
    }

```
2.配置本地host
```
127.0.0.1	www.push.com
```
3.访问地址
http://www.push.com
访问文件webroot/index.php