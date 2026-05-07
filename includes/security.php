<?php
/**
 * 安全处理类
 * 包含密码加密、验证等安全相关功能
 */

class Security {
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
    }
    
    /**
     * 加密密码
     */
    public function hashPassword(string $password): string {
        return password_hash(
            $password,
            $this->config['security']['password_algorithm'],
            ['cost' => $this->config['security']['password_cost']]
        );
    }
    
    /**
     * 验证密码
     */
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * 生成邮箱验证token
     */
    public function generateEmailToken(): string {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * 验证邮箱格式和安全性
     */
    public function validateEmail(string $email): array {
        $errors = [];
        
        // 基本格式验证
        if (!isValidEmail($email)) {
            $errors[] = '邮箱格式不正确';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // 长度限制
        if (strlen($email) > 100) {
            $errors[] = '邮箱地址过长';
        }
        
        // 检查是否为临时邮箱（可选）
        $tempDomains = ['tempmail.com', 'throwaway.com', 'guerrillamail.com'];
        $domain = substr(strrchr($email, '@'), 1);
        if (in_array(strtolower($domain), $tempDomains)) {
            $errors[] = '不支持临时邮箱';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * 验证密码强度
     */
    public function validatePassword(string $password): array {
        $errors = [];
        
        if (strlen($password) < 6) {
            $errors[] = '密码至少需要6个字符';
        }
        
        if (strlen($password) > 50) {
            $errors[] = '密码不能超过50个字符';
        }
        
        // 检查常见弱密码
        $commonPasswords = ['123456', 'password', '123456789', '12345678', '12345'];
        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = '密码过于简单，请使用更复杂的密码';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculatePasswordStrength($password)
        ];
    }
    
    /**
     * 计算密码强度等级
     */
    private function calculatePasswordStrength(string $password): int {
        $strength = 0;
        
        // 长度加分
        if (strlen($password) >= 8) $strength += 1;
        if (strlen($password) >= 12) $strength += 1;
        
        // 包含数字
        if (preg_match('/\d/', $password)) $strength += 1;
        
        // 包含小写字母
        if (preg_match('/[a-z]/', $password)) $strength += 1;
        
        // 包含大写字母
        if (preg_match('/[A-Z]/', $password)) $strength += 1;
        
        // 包含特殊字符
        if (preg_match('/[^a-zA-Z\d]/', $password)) $strength += 1;
        
        return min($strength, 5); // 最大5级
    }
    
    /**
     * 清理用户输入
     */
    public function sanitizeInput(string $input): string {
        $input = trim($input);
        $input = stripslashes($input);
        return $input;
    }
    
    /**
     * 生成安全的文件名
     */
    public function generateSecureFileName(string $originalName, string $prefix = ''): string {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return $prefix . '_' . $timestamp . '_' . random_int(1000, 9999) . '.' . $ext;
    }
}
