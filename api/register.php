<?php
/**
 * 注册API - 昵称+密码
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') errorResponse('请求方法不允许', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) errorResponse('无效的请求数据');

$nickname = trim($input['nickname'] ?? '');
$password = $input['password'] ?? '';

if (empty($nickname) || empty($password)) errorResponse('请填写昵称和密码');
if (mb_strlen($nickname) < 2 || mb_strlen($nickname) > 20) errorResponse('昵称需要2-20个字符');
if (strlen($password) < 6) errorResponse('密码至少6位');

try {
    $db = Database::getInstance();

    $existing = $db->queryOne("SELECT id FROM users WHERE nickname = ?", [$nickname]);
    if ($existing) errorResponse('该昵称已被使用');

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $db->execute("INSERT INTO users (nickname, password_hash, status) VALUES (?, ?, 1)", [$nickname, $hashedPassword]);
    $userId = (int)$db->lastInsertId();

    session_start();
    $_SESSION['user_id'] = $userId;
    $_SESSION['nickname'] = $nickname;

    successResponse('注册成功', ['user_id' => $userId, 'nickname' => $nickname]);
} catch (Exception $e) {
    errorResponse('注册失败：' . $e->getMessage(), 500);
}
