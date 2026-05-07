<?php
/**
 * 查看用户证据图片
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$userId = intval($_GET['id'] ?? 0);
if ($userId <= 0) die('无效参数');

try {
    $db = Database::getInstance();
    $user = $db->queryOne("SELECT image_path FROM users WHERE id = ?", [$userId]);

    if (!$user || empty($user['image_path'])) {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>无图片</title></head><body style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;color:#9ca3af;">该用户未上传证据图片</body></html>';
        exit;
    }

    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($user['image_path'], '/\\');

    if (!file_exists($filePath)) {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>图片不存在</title></head><body style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;color:#ef4444;">图片文件不存在</body></html>';
        exit;
    }

    $mime = mime_content_type($filePath);
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);

} catch (Exception $e) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>错误</title></head><body style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;color:#ef4444;">加载失败</body></html>';
}
