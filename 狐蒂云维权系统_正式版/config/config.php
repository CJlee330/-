<?php
/**
 * 全局配置文件
 */

return [
    // 系统配置
    'app_name' => '狐蒂云维权系统',
    'app_version' => '1.0.0',
    'app_url' => 'http://hdy.544442018.xyz', // 请修改为实际域名
    
    // 安全配置
    'security' => [
        // Session配置
        'session_name' => 'hudi_cloud_session',
        'session_lifetime' => 86400, // 24小时
        'session_httponly' => true,
        'session_secure' => false, // HTTPS时设为true
        
        // CSRF Token配置
        'csrf_token_name' => 'csrf_token',
        'csrf_token_expire' => 3600, // 1小时
        
        // 密码加密配置
        'password_algorithm' => PASSWORD_BCRYPT,
        'password_cost' => 12,
    ],
    
    // 文件上传配置
    'upload' => [
        // 允许的文件类型
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
        // 最大文件大小（字节）5MB
        'max_size' => 5 * 1024 * 1024,
        // 上传目录
        'directory' => __DIR__ . '/../uploads/',
        // 图片最大尺寸（像素）
        'max_width' => 2000,
        'max_height' => 2000,
        // 缩略图宽度
        'thumbnail_width' => 300,
    ],
    
    // 设备指纹配置
    'fingerprint' => [
        // Device ID长度
        'device_id_length' => 32,
        // Cookie名称
        'cookie_name' => 'device_id',
        // Cookie有效期（秒）1年
        'cookie_expire' => 365 * 24 * 60 * 60,
    ],
];
