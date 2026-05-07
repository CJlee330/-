<?php
/**
 * 公共函数库
 */

/**
 * JSON响应输出
 */
function jsonResponse(int $code, string $message, $data = null): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 成功响应
 */
function successResponse(string $message = '操作成功', $data = null): void {
    jsonResponse(200, $message, $data);
}

/**
 * 错误响应
 */
function errorResponse(string $message = '操作失败', int $code = 400, $data = null): void {
    jsonResponse($code, $message, $data);
}

/**
 * 生成CSRF Token
 */
function generateCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $config = require __DIR__ . '/../config/config.php';
    $tokenName = $config['security']['csrf_token_name'];
    
    if (empty($_SESSION[$tokenName])) {
        $_SESSION[$tokenName] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION[$tokenName];
}

/**
 * 验证CSRF Token
 */
function validateCSRFToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $config = require __DIR__ . '/../config/config.php';
    $tokenName = $config['security']['csrf_token_name'];
    
    return isset($_SESSION[$tokenName]) && hash_equals($_SESSION[$tokenName], $token);
}

/**
 * 获取客户端真实IP
 */
function getClientIP(): string {
    $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * 验证邮箱格式
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 验证IP地址格式
 */
function isValidIP(string $ip): bool {
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

/**
 * 验证密码强度（至少6位）
 */
function isValidPassword(string $password): bool {
    return strlen($password) >= 6;
}

/**
 * XSS过滤
 */
function xssClean(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 生成随机字符串
 */
function generateRandomString(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

/**
 * 格式化文件大小
 */
function formatFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * 记录日志
 */
function logMessage(string $message, string $level = 'info'): void {
    $logDir = __DIR__ . '/../logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
