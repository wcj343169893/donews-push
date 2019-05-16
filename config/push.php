<?php

/**
 *获取环境变量
 * @param string $name
 * @param string $default
 * @return string
 */
function env($name, $default)
{
    $value = getenv($name);
    if (! $value) {
        $value = $default;
    }
    return $value;
}
return [
    'redis' => [
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 2)
        ]
    ],
    "pkgname" => env("APP_PKG_NAME", ""),
    "platform" => [
        "xiaomi" => [
            "appSecret" => env("XIAOMI_APP_SECRET", null),
            "intentUri" => env("XIAOMI_APP_INTENT_URI", null),
            "httpSendType" => env("XIAOMI_APP_SEND_TYPE", "alias")
        ],
        
        "umeng" => [
            "appKey" => env("UMENG_APP_KEY", null),
            "appSecret" => env("UMENG_APP_MASTER_SECRET", null)
        ],
        
        "huawei" => [
            "appId" => env("HUAWEI_CLIENT_ID", null),
            "appSecret" => env("HUAWEI_CLIENT_SECRET", null),
            'intentUri' => env('HUAWEI_APP_INTENT', null)
        ],
        
        "apple" => [
            "appId" => env("APNS_CERTIFICATE_PATH", null),
            "appSecret" => env("APNS_CERTIFICATE_PASSPHRASE", null),
            "appEnvironment" => env("APNS_ENVIRONMENT", "sandbox") // production
        ],
        
        "vivo" => [
            "appId" => env("VIVO_APP_ID", null),
            "appKey" => env("VIVO_APP_KEY", null),
            "appSecret" => env("VIVO_APP_SECRET", null)
        ],
        
        "meizu" => [
            "appId" => env("MEIZU_APP_ID", null),
            "appSecret" => env("MEIZU_APP_SECRET", null)
        ],
        
        "oppo" => [
            "appKey" => env("OPPO_APP_KEY", null),
            "appSecret" => env("OPPO_MASTER_SECRET", null)
        ]
    ]
];