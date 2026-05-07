<?php
/**
 * 登录API - 昵称+密码
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') errorResponse('请求方法不允许', 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) errorResponse('无效的请求数据');

$nickname = trim($input['nickname'] ?? '');
$password = $input['password'] ?? '';

if (empty($nickname) || empty($password)) errorResponse('请填写昵称和密码');

try {
    $db = Database::getInstance();

    $user = $db->queryOne("SELECT * FROM users WHERE nickname = ?", [$nickname]);
    if (!$user || empty($user['password_hash'])) errorResponse('该用户不存在');

    if (!password_verify($password, $user['password_hash'])) errorResponse('密码错误');

    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nickname'] = $user['nickname'];

    successResponse('登录成功', [
        'user_data' => [
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'server_ip' => $user['server_ip'],
            'order_number' => $user['order_number'],
            'purchase_duration' => $user['purchase_duration'],
            'remaining_time' => $user['remaining_time'],
            'refund_amount' => $user['refund_amount'],
            'status' => $user['status'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at']
        ]
    ]);
} catch (Exception $e) {
    errorResponse('系统错误：' . $e->getMessage(), 500);
}
