<?php
/**
 * 下载当前用户维权信息CSV
 */

session_start();
if (empty($_SESSION['user_id'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<script>alert("请先登录");history.back();</script>';
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$userId = (int)$_SESSION['user_id'];

try {
    $db = Database::getInstance();
    
    $u = $db->queryOne(
        "SELECT nickname, server_ip, order_number, purchase_duration, remaining_time, 
                refund_amount, image_path, remark, status, created_at 
         FROM users WHERE id = ?",
        [$userId]
    );
    
    if (!$u) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<script>alert("用户数据不存在");history.back();</script>';
        exit;
    }

    $imgUrl = !empty($u['image_path']) ? ('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($u['image_path'], '/\\')) : '未上传';
    $statusText = $u['status'] == 1 ? '已通过' : '审核中';

    $rows = [
        ['项目', '内容'],
        ['昵称', $u['nickname']],
        ['服务器IP', $u['server_ip'] ?: '未填写'],
        ['订单号', $u['order_number'] ?: '未填写'],
        ['购买时长', $u['purchase_duration'] ?: '未填写'],
        ['剩余时长', $u['remaining_time'] ?: '未填写'],
        ['退款金额(元)', $u['refund_amount']],
        ['备注', $u['remark'] ?: '无'],
        ['证据图片', $imgUrl],
        ['状态', $statusText],
        ['提交时间', $u['created_at']]
    ];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="my_rights_data_' . date('Ymd') . '.csv"');

    // BOM让Excel识别UTF-8
    echo "\xEF\xBB\xBF";

    foreach ($rows as $r) {
        $line = [];
        foreach ($r as $v) {
            $v = str_replace(['"', "\n", "\r"], ['', '', ''], (string)$v);
            $line[] = '"' . $v . '"';
        }
        echo implode(',', $line) . "\n";
    }

} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<script>alert("下载失败：' . addslashes($e->getMessage()) . '");history.back();</script>';
}
