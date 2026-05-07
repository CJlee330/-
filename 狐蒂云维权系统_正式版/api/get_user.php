<?php
/**
 * 获取当前登录用户信息
 */

header('Content-Type: application/json; charset=utf-8');
session_start();
if (empty($_SESSION['user_id'])) errorResponse('未登录', 401);

try {
    $db = Database::getInstance();
    $user = $db->queryOne(
        "SELECT id, nickname, server_ip, order_number, purchase_duration, remaining_time, refund_amount, image_path, remark, status, created_at, updated_at FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
    if (!$user) errorResponse('用户不存在', 404);

    successResponse('获取成功', $user);
} catch (Exception $e) {
    errorResponse('系统错误', 500);
}
