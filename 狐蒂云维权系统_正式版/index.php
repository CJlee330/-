<?php
/**
 * 狐蒂云维权系统 - 主页面 (最终版)
 */

session_start();
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>狐蒂云维权系统</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container">

    <header class="header">
        <h1>🦊 狐蒂云维权系统</h1>
		<p class="subtitle">本维权系统专为 2026 年 5 月深圳狐蒂云公司跑路事件量身搭建。</p>
		<p class="subtitle">待各位受害者证据提交齐全后，系统留存的全部维权数据，将统一上交相关部门，或交由自愿牵头为大家走法律途径的维权代表。</p>
        <p class="subtitle">个人敏感信息全程保密不予公示，大家可随时自行下载自己提交的维权材料。</p>
		<p class="subtitle">狐蒂云维权初步金额统计表格链接如下 https://www.kdocs.cn/l/clCugGuk38Kb </p>
		<p class="subtitle">狐蒂跑路云2000人维权群 1093925113 </p>
		<p class="subtitle">本系统仅用于本次事件维权，禁止任何形式广告发布。</p>
    </header>

    <!-- 统计栏 -->
    <div id="stats-banner" class="stats-banner">
        <div class="stats-header">
            <span class="stats-title">📊 维权进展实时统计</span>
            <button class="btn-refresh-stats" onclick="refreshPublicStats()" title="刷新数据">🔄</button>
        </div>
        <div class="stats-cards" id="publicStatsCards">
            <div class="stat-card-loading">正在加载数据...</div>
        </div>
    </div>

    <!-- 用户实时反馈 -->
    <div class="panel" id="feedback-panel">
        <div class="panel-header">
            <h3>💬 用户实时反馈</h3>
            <span id="feedback-count" style="color:#6b7280;font-size:13px;"></span>
        </div>
        
        <div id="feedback-list" class="feedback-list">
            <div class="stat-card-loading">正在加载...</div>
        </div>
        
        <div id="feedback-pagination" class="feedback-pagination" style="display:none;"></div>
    </div>

    <!-- 登录/注册区域 -->
    <?php $isLoggedIn = !empty($_SESSION['user_id']); ?>
    
    <?php if (!$isLoggedIn): ?>

    <!-- 未登录：显示登录/注册 -->
    <div class="panel">
        <div class="panel-header">
            <h3>🔑 登录 / 注册</h3>
        </div>

        <!-- 登录表单 -->
        <form id="login-form" class="form">
            <div class="form-group">
                <label for="login-nickname">昵称 *</label>
                <input type="text" id="login-nickname" required placeholder="支持中文、字母、数字">
            </div>
            <div class="form-group">
                <label for="login-password">密码 *</label>
                <input type="password" id="login-password" required placeholder="至少6位">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block btn-lg">登 录</button>
            </div>
            <div style="text-align:center;margin-top:12px;">
                没有账号？<a href="#" onclick="showRegisterForm()">立即注册</a>
            </div>
        </form>

        <!-- 注册表单（默认隐藏） -->
        <form id="register-form" class="form" style="display:none;">
            <div class="form-group">
                <label for="reg-nickname">设置昵称 *</label>
                <input type="text" id="reg-nickname" required placeholder="支持中文、字母、数字，2-20字">
            </div>
            <div class="form-group">
                <label for="reg-password">设置密码 *</label>
                <input type="password" id="reg-password" required placeholder="至少6位字符" minlength="6">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success btn-block btn-lg">注 册</button>
            </div>
            <div style="text-align:center;margin-top:12px;">
                已有账号？<a href="#" onclick="showLoginForm()">返回登录</a>
            </div>
        </form>

        <div id="auth-message" class="form-message" style="display:none;"></div>
    </div>

    <?php else: ?>

    <!-- 已登录：显示内容 + 退出 -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;padding:12px 18px;background:#fff;border-radius:10px;border:1px solid #e5e7eb;">
        <span style="color:#dc2626;font-size:18px;font-weight:700;">✓ 已登录为：<strong><?php echo htmlspecialchars($_SESSION['nickname']); ?></strong></span>
        <div style="display:flex;gap:8px;">
            <button onclick="downloadMyData(this)" class="btn-download-mydata" 
                data-nickname="<?php echo htmlspecialchars($_SESSION['nickname']); ?>"
                data-user-id="<?php echo (int)$_SESSION['user_id']; ?>">📥 下载我的信息</button>
            <button onclick="handleLogout()" class="btn-logout-custom">退出登录</button>
        </div>
    </div>

    <!-- 表单区域 -->
    <div class="panel" id="claim-panel">
        <div class="panel-header" style="padding:14px 18px;">
            <h3 style="font-size:16px;">📝 填写维权信息</h3>
        </div>
        
        <form id="claim-form" class="form" style="padding:12px 18px;">
            <div class="form-compact-grid">

                <div class="form-group-compact">
                    <label for="server_ip">服务器IP</label>
                    <input type="text" id="server_ip" name="server_ip" placeholder="192.168.1.100">
                </div>

                <div class="form-group-compact">
                    <label for="order_number">订单号</label>
                    <input type="text" id="order_number" name="order_number" maxlength="100" placeholder="HD20240101001">
                </div>

                <div class="form-group-compact">
                    <label for="purchase_duration">购买时长</label>
                    <select id="purchase_duration" name="purchase_duration">
                        <option value="">不填</option>
                        <option value="1年">1年</option>
                        <option value="2年">2年</option>
                        <option value="3年">3年</option>
                        <option value="10年">10年</option>
                        <option value="永久">永久</option>
                        <option value="other">其他</option>
                    </select>
                </div>

                <div class="form-group-compact">
                    <label for="remaining_time">剩余时长</label>
                    <input type="text" id="remaining_time" name="remaining_time" maxlength="50" placeholder="8个月15天">
                </div>

            </div>

            <div style="display:flex;gap:12px;margin-bottom:10px;">
                <div class="form-group-compact" style="flex:1;">
                    <label for="refund_amount">退款金额 (元) <span class="required">*</span></label>
                    <input type="number" id="refund_amount" name="refund_amount" required min="0" step="0.01" placeholder="必填">
                </div>
                
                <div class="upload-area-simple" id="image-upload-area" style="flex:0 0 140px;padding:8px 10px;">
                    <input type="file" id="evidence-image" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                    <div id="image-placeholder" class="upload-ph">
                        <div style="font-size:24px;">📷</div>
                        <small>上传图片(≤2MB)</small>
                    </div>
                    <div id="image-preview-wrap" style="display:none;position:relative;">
                        <img id="image-preview-img" src="" alt="" style="max-width:120px;max-height:80px;border-radius:6px;cursor:pointer;" onclick="window.open(this.src)">
                        <button type="button" onclick="removeImage()" style="position:absolute;top:-4px;right:-4px;width:20px;height:20px;background:#dc2626;color:#fff;border:none;border-radius:50%;cursor:pointer;font-size:11px;line-height:20px;text-align:center;">✕</button>
                    </div>
                </div>
            </div>

            <div class="form-group-compact" style="margin-bottom:12px;">
                <label for="remark">文字备注（选填）</label>
                <textarea id="remark" name="remark" rows="2" maxlength="500" placeholder="补充说明、联系方式等信息..." style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:13px;resize:vertical;font-family:inherit;"></textarea>
            </div>

            <div class="form-actions" style="margin-top:4px;">
                <button type="submit" id="btn-submit" class="btn btn-primary btn-block" style="padding:12px;">✓ 提交维权信息</button>
            </div>
            
            <div id="form-message" class="form-message" style="display:none;"></div>
        </form>
    </div>

    <!-- 已提交信息展示 -->
    <div id="submitted-info" class="panel" style="display: none;">
        <div class="panel-header" style="padding:14px 18px;">
            <h3 style="font-size:16px;display:inline;">✅ 您的维权信息</h3><span style="color:#9ca3af;font-size:12px;margin-left:8px;"><span id="info-submitted-time"></span></span>
        </div>
        
        <table class="my-data-table" style="width:100%;border-collapse:collapse;">
            <tr><td class="mdl">服务器IP</td><td id="info-server-ip">-</td></tr>
            <tr><td class="mdl">订单号</td><td id="info-order-number">-</td></tr>
            <tr><td class="mdl">购买时长</td><td id="info-purchase-duration">-</td></tr>
            <tr><td class="mdl">剩余时长</td><td id="info-remaining-time">-</td></tr>
            <tr><td class="mdl">退款金额</td><td id="info-refund-amount" style="font-weight:700;color:#dc2626;"></td></tr>
            <tr><td class="mdl">备注</td><td id="info-remark">-</td></tr>
            <tr><td class="mdl">状态</td><td id="info-status" class="status-badge"></td></tr>
        </table>

        <div class="form-actions mt-20" style="padding:0 18px 18px;">
            <button type="button" id="btn-edit-info" class="btn btn-warning btn-block" style="padding:10px;">✏️ 修改信息</button>
        </div>
    </div>

    <?php endif; ?>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> 狐蒂云维权系统</p>
    </footer>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
