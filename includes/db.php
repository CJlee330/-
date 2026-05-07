<?php
/**
 * 数据库连接类
 * 使用PDO实现数据库连接和操作
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE {$config['collation']}",
        ];
        
        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            error_log("数据库连接失败: " . $e->getMessage());
            throw new Exception("数据库连接失败，请检查配置");
        }
    }
    
    /**
     * 获取单例实例
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 获取PDO连接
     */
    public function getConnection(): PDO {
        return $this->pdo;
    }
    
    /**
     * 执行查询并返回所有结果
     */
    public function queryAll(string $sql, array $params = []): array {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * 执行查询并返回单条结果
     */
    public function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->prepareAndExecute($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * 执行插入/更新/删除操作，返回受影响行数
     */
    public function execute(string $sql, array $params = []): int {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * 执行预处理语句并返回PDOStatement对象（内部方法）
     */
    private function prepareAndExecute(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * 获取最后插入的ID
     */
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * 开始事务
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * 提交事务
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }
    
    /**
     * 回滚事务
     */
    public function rollback(): bool {
        return $this->pdo->rollBack();
    }
    
    /**
     * 禁止克隆
     */
    private function __clone() {}
}
