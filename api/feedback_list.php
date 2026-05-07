<?php
/**
 * 用户实时反馈API - 分页展示 + 图片标记
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$page = max(1, intval($_GET['page'] ?? 1));
$pageSize = 10;
$offset = ($page - 1) * $pageSize;

try {
    $db = Database::getInstance();

    $total = (int)$db->queryOne("SELECT COUNT(*) as c FROM users WHERE refund_amount > 0")['c'];

    $rows = $db->queryAll(
        "SELECT id, nickname, purchase_duration, remaining_time, refund_amount, 
                image_path IS NOT NULL AND image_path != '' as has_image,
                remark, created_at 
         FROM users WHERE refund_amount > 0 
         ORDER BY created_at DESC LIMIT ? OFFSET ?",
        [$pageSize, $offset]
    );

    $list = [];
    foreach ($rows as $r) {
        $nick = $r['nickname'];
        if (mb_strlen($nick) >= 4) {
            $nick = mb_substr($nick, 0, 2) . '**';
        }
        $list[] = [
            'id' => (int)$r['id'],
            'nickname' => $nick,
            'purchase_duration' => $r['purchase_duration'] ?: '-',
            'remaining_time' => $r['remaining_time'] ?: '-',
            'refund_amount' => (float)$r['refund_amount'],
            'has_image' => (bool)$r['has_image'],
            'remark' => $r['remark'] ?: '',
            'created_at' => $r['created_at']
        ];
    }

    successResponse('ok', [
        'list' => $list,
        'total' => $total,
        'page' => $page,
        'page_size' => $pageSize,
        'total_pages' => ceil($total / $pageSize)
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['code'=>200,'message'=>'ok','data'=>['list'=>[],'total'=>0,'page'=>1,'page_size'=>10,'total_pages'=>0]]);
}
