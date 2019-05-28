<?php

/**
 *获取环境变量,避免与其他地方的冲突
 * @param string $name
 * @param string $default
 * @return string
 */
if (!function_exists('envData')) {
    function envData($name, $default)
    {
        $value = getenv($name);
        if (! $value) {
            $value = $default;
        }
        return $value;
    }
}
return [
    'redis' => [
        'default' => [
            'host' => envData('REDIS_HOST', '127.0.0.1'),
            'password' => envData('REDIS_PASSWORD', null),
            'port' => envData('REDIS_PORT', 6379),
            'database' => envData('REDIS_DATABASE', 2)
        ]
    ],
    "pkgname" => envData("APP_PKG_NAME", ""),
    "platform" => [
        "xiaomi" => [
            "appSecret" => envData("XIAOMI_APP_SECRET", null),
            "intentUri" => envData("XIAOMI_APP_INTENT_URI", null),
            "httpSendType" => envData("XIAOMI_APP_SEND_TYPE", "alias")
        ],
        
        "umeng" => [
            "appKey" => envData("UMENG_APP_KEY", null),
            "appSecret" => envData("UMENG_APP_MASTER_SECRET", null)
        ],
        
        "huawei" => [
            "appId" => envData("HUAWEI_CLIENT_ID", null),
            "appSecret" => envData("HUAWEI_CLIENT_SECRET", null),
            'intentUri' => envData('HUAWEI_APP_INTENT', null)
        ],
        
        "apple" => [
            "appId" => envData("APNS_CERTIFICATE_PATH", null),
            "appSecret" => envData("APNS_CERTIFICATE_PASSPHRASE", null),
            "appEnvironment" => envData("APNS_ENVIRONMENT", "sandbox") // production
        ],
        
        "vivo" => [
            "appId" => envData("VIVO_APP_ID", null),
            "appKey" => envData("VIVO_APP_KEY", null),
            "appSecret" => envData("VIVO_APP_SECRET", null),
            "httpSendType" => envData("VIVO_APP_SEND_TYPE", "alias"),
        ],
        
        "meizu" => [
            "appId" => envData("MEIZU_APP_ID", null),
            "appSecret" => envData("MEIZU_APP_SECRET", null)
        ],
        
        "oppo" => [
            "appId" => envData("OPPO_APP_ID", null),
            "appKey" => envData("OPPO_APP_KEY", null),
            "appSecret" => envData("OPPO_APP_SECRET", null),
            "masterSecret" => envData("OPPO_MASTER_SECRET", null),
            "intentUri" => envData("OPPO_APP_INTENT_URI", null),
            "httpSendType" => envData("OPPO_APP_SEND_TYPE", null)
        ]
    ]
];