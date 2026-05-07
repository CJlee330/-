<?php
/**
 * 提交维权信息 - 支持图片上传
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

if (empty($_SESSION['user_id'])) errorResponse('请先登录', 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') errorResponse('请求方法不允许', 405);

$userId = $_SESSION['user_id'];

$refundAmount = isset($_POST['refund_amount']) ? floatval($_POST['refund_amount']) : 0;
if ($refundAmount <= 0) errorResponse('退款金额必须大于0');

try {
    $db = Database::getInstance();
    $config = require __DIR__ . '/../config/config.php';

    // 处理图片上传
    $imagePath = null;
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) errorResponse('只支持 JPG/PNG/GIF/WebP 格式');
        if ($file['size'] > 2 * 1024 * 1024) errorResponse('图片不能超过 2MB');

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'evidence_' . $userId . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
        $uploadDir = rtrim($config['upload']['directory'], '/');
        
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $targetPath = $uploadDir . '/' . $fileName;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $imagePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $targetPath);
            // 兼容Windows路径
            $imagePath = str_replace('\\', '/', $imagePath);
        }
    }

    $db->beginTransaction();

    $db->execute(
        "UPDATE users SET
            server_ip = ?,
            order_number = ?,
            purchase_duration = ?,
            remaining_time = ?,
            refund_amount = ?,
            image_path = ?,
            remark = ?,
            status = 1,
            updated_at = NOW()
         WHERE id = ?",
        [
            trim($_POST['server_ip'] ?? ''),
            trim($_POST['order_number'] ?? ''),
            $_POST['purchase_duration'] ?? '',
            trim($_POST['remaining_time'] ?? ''),
            $refundAmount,
            $imagePath,
            $_POST['remark'] ?? '',
            $userId
        ]
    );

    $db->commit();
    successResponse('提交成功，已自动通过审核');

} catch (Exception $e) {
    $db->rollBack();
    errorResponse('提交失败：' . $e->getMessage(), 500);
}
