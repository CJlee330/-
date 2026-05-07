/**
 * 主逻辑脚本 (最终版)
 * 昵称+密码登录，只有退款金额必填
 */

class App {
    constructor() {
        this.currentUser = null;
        this.isLoggedIn = false;
        this.uploadedImageFile = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadPublicStats();
        this.loadFeedback(1);

        if (document.getElementById('claim-panel')) {
            this.checkLoginStatus();
        }
    }

    async checkLoginStatus() {
        try {
            const res = await fetch('/api/get_user.php');
            const result = await res.json();
            if (result.code === 200 && result.data) {
                this.isLoggedIn = true;
                this.currentUser = result.data;
                if (this.currentUser.refund_amount > 0) this.showSubmittedInfo();
            }
        } catch(e) {}
    }

    bindEvents() {
        document.getElementById('login-form')?.addEventListener('submit', e => { e.preventDefault(); this.handleLogin(); });
        document.getElementById('register-form')?.addEventListener('submit', e => { e.preventDefault(); this.handleRegister(); });
        document.getElementById('claim-form')?.addEventListener('submit', e => { e.preventDefault(); this.handleSubmitClaim(); });
        document.getElementById('btn-edit-info')?.addEventListener('click', () => this.showFormAndFillData());

        // 图片上传
        const uploadArea = document.getElementById('image-upload-area');
        const fileInput = document.getElementById('evidence-image');
        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());
            uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.style.borderColor='#3b82f6'; });
            uploadArea.addEventListener('dragleave', () => uploadArea.style.borderColor='#d1d5db');
            uploadArea.addEventListener('drop', e => {
                e.preventDefault();
                uploadArea.style.borderColor='#d1d5db';
                if (e.dataTransfer.files[0]) this.handleImageSelect(e.dataTransfer.files[0]);
            });
            fileInput.addEventListener('change', () => {
                if (fileInput.files[0]) this.handleImageSelect(fileInput.files[0]);
            });
        }
    }

    handleImageSelect(file) {
        const msgEl = document.getElementById('image-upload-msg');
        const allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
        
        if (!allowedTypes.includes(file.type)) return this.showMsg(msgEl, '❌ 只支持 JPG/PNG/GIF/WebP 格式', 'error');
        if (file.size > 2 * 1024 * 1024) return this.showMsg(msgEl, '❌ 图片不能超过 2MB', 'error');

        this.uploadedImageFile = file;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('image-preview-img').src = e.target.result;
            document.getElementById('image-placeholder').style.display = 'none';
            document.getElementById('image-preview-wrap').style.display = 'block';
            if (msgEl) { msgEl.textContent = ''; msgEl.className = ''; }
        };
        reader.readAsDataURL(file);
    }

    async handleLogin() {
        const nickname = document.getElementById('login-nickname').value.trim();
        const password = document.getElementById('login-password').value;
        const msgDiv = document.getElementById('auth-message');
        if (!nickname || !password) return this.showMsg(msgDiv, '请填写完整', 'error');

        try {
            const res = await fetch('/api/login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({nickname, password})
            });
            const r = await res.json();
            if (r.code === 200) { this.showMsg(msgDiv, '✅ 登录成功！', 'success'); setTimeout(() => location.reload(), 800); }
            else this.showMsg(msgDiv, '❌ ' + r.message, 'error');
        } catch(err) { this.showMsg(msgDiv, '❌ 失败：' + err.message, 'error'); }
    }

    async handleRegister() {
        const nickname = document.getElementById('reg-nickname').value.trim();
        const password = document.getElementById('reg-password').value;
        const msgDiv = document.getElementById('auth-message');
        if (!nickname || !password) return this.showMsg(msgDiv, '请填写完整', 'error');
        if (nickname.length < 2 || nickname.length > 20) return this.showMsg(msgDiv, '昵称2-20个字符', 'error');
        if (password.length < 6) return this.showMsg(msgDiv, '密码至少6位', 'error');

        try {
            const res = await fetch('/api/register.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({nickname, password})
            });
            const r = await res.json();
            if (r.code === 200) { this.showMsg(msgDiv, '✅ 注册成功！', 'success'); setTimeout(() => location.reload(), 800); }
            else this.showMsg(msgDiv, '❌ ' + r.message, 'error');
        } catch(err) { this.showMsg(msgDiv, '❌ 失败：' + err.message, 'error'); }
    }

    async handleSubmitClaim() {
        const refundAmount = document.getElementById('refund_amount').value;

        if (!refundAmount || isNaN(refundAmount) || parseFloat(refundAmount) <= 0) {
            return this.showMessage(document.getElementById('form-message'), '请填写有效的退款金额', 'error');
        }

        const formData = {
            server_ip: document.getElementById('server_ip')?.value.trim() || '',
            order_number: document.getElementById('order_number')?.value.trim() || '',
            purchase_duration: document.getElementById('purchase_duration')?.value || '',
            remaining_time: document.getElementById('remaining_time')?.value.trim() || '',
            refund_amount: parseFloat(refundAmount)
        };

        const btn = document.getElementById('btn-submit');
        const msgDiv = document.getElementById('form-message');

        try {
            btn.disabled = true; btn.textContent = '提交中...';

            const fd = new FormData();
            fd.append('server_ip', document.getElementById('server_ip')?.value.trim() || '');
            fd.append('order_number', document.getElementById('order_number')?.value.trim() || '');
            fd.append('purchase_duration', document.getElementById('purchase_duration')?.value || '');
            fd.append('remaining_time', document.getElementById('remaining_time')?.value.trim() || '');
            fd.append('refund_amount', parseFloat(refundAmount));
            fd.append('remark', document.getElementById('remark')?.value.trim() || '');
            if (this.uploadedImageFile) fd.append('image', this.uploadedImageFile);

            const res = await fetch('/api/submit.php', {
                method: 'POST',
                body: fd
            });

            const result = await res.json();
            if (result.code === 200) {
                this.showMessage(msgDiv, '✅ 提交成功！已自动通过审核。', 'success');
                setTimeout(() => { this.loadUserData(); this.hideMessage(msgDiv); }, 1200);
            } else throw new Error(result.message);
        } catch(err) {
            this.showMessage(msgDiv, '❌ ' + err.message, 'error');
        } finally {
            btn.disabled = false; btn.textContent = '✓ 提交维权信息';
        }
    }

    async loadUserData() {
        try {
            const res = await fetch('/api/get_user.php');
            const result = await res.json();
            if (result.code === 200 && result.data) {
                this.currentUser = result.data;
                if (this.currentUser.refund_amount > 0) this.showSubmittedInfo();
            }
        } catch(e) {}
    }

    showSubmittedInfo() {
        document.getElementById('claim-panel').style.display = 'none';
        document.getElementById('submitted-info').style.display = 'block';

        const u = this.currentUser;
        document.getElementById('info-server-ip').textContent = u.server_ip || '-';
        document.getElementById('info-order-number').textContent = u.order_number || '-';
        document.getElementById('info-purchase-duration').textContent = u.purchase_duration || '-';
        document.getElementById('info-remaining-time').textContent = u.remaining_time || '-';
        document.getElementById('info-refund-amount').textContent = '¥' + parseFloat(u.refund_amount).toFixed(2);
        document.getElementById('info-remark').textContent = u.remark || '-';
        document.getElementById('info-submitted-time').textContent = u.updated_at;

        const el = document.getElementById('info-status');
        el.textContent = u.status == 1 ? '✅ 已通过' : '⏳ 审核中';
        el.className = 'status-badge status-' + (u.status == 1 ? 'success' : 'warning');
    }

    showFormAndFillData() {
        document.getElementById('submitted-info').style.display = 'none';
        document.getElementById('claim-panel').style.display = 'block';
        if (this.currentUser) {
            if (this.currentUser.server_ip) document.getElementById('server_ip').value = this.currentUser.server_ip;
            if (this.currentUser.order_number) document.getElementById('order_number').value = this.currentUser.order_number;
            if (this.currentUser.purchase_duration) document.getElementById('purchase_duration').value = this.currentUser.purchase_duration;
            if (this.currentUser.remaining_time) document.getElementById('remaining_time').value = this.currentUser.remaining_time;
            if (this.currentUser.refund_amount > 0) document.getElementById('refund_amount').value = this.currentUser.refund_amount;
            if (this.currentUser.remark) document.getElementById('remark').value = this.currentUser.remark;
        }
    }

    showMsg(el, html, type) { el.innerHTML = html; el.className = `form-message ${type}`; el.style.display = 'block'; }
    showMessage(el, html, type) { el.innerHTML = html; el.className = `form-message ${type}`; el.style.display = 'block'; }
    hideMessage(el) { el.style.display = 'none'; el.innerHTML = ''; }

    async loadPublicStats() {
        try {
            const data = await this.fetchAPI('/api/public_stats.php?action=overview');
            this.renderStatCards(data);
        } catch(e) {}
    }

    async loadFeedback(page) {
        try {
            const res = await fetch(`/api/feedback_list.php?page=${page}`);
            const result = await res.json();
            if (result.code === 200 && result.data) this.renderFeedback(result.data, page);
        } catch(e) {}
    }

    renderFeedback(data, page) {
        const listEl = document.getElementById('feedback-list');
        const countEl = document.getElementById('feedback-count');
        const pagEl = document.getElementById('feedback-pagination');

        if (!listEl) return;

        countEl.textContent = `共 ${data.total} 条反馈`;

        if (!data.list || data.list.length === 0) {
            listEl.innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af;">暂无反馈数据</div>';
            pagEl.style.display = 'none';
            return;
        }

        let html = '<table class="feedback-table"><thead><tr><th>昵称</th><th>购买时长</th><th>剩余时长</th><th>退款金额</th><th>备注</th></tr></thead><tbody>';

        data.list.forEach((item, i) => {
            const evidenceBtn = item.has_image ? `<button onclick="viewEvidence(${item.id})" class="btn-evidence">📷</button>` : '';
            html += `<tr>
                <td class="td-nick">${item.nickname} ${evidenceBtn}</td>
                <td>${item.purchase_duration}</td>
                <td>${item.remaining_time}</td>
                <td class="td-amount">¥${parseFloat(item.refund_amount).toLocaleString()}</td>
                <td style="font-size:12px;color:#6b7280;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${(item.remark||'').replace(/"/g,'&quot;')}">${item.remark || '-'}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        listEl.innerHTML = html;

        if (data.total_pages > 1) {
            pagEl.style.display = 'flex';
            let pHtml = '';
            if (page > 1) pHtml += `<button onclick="window.app.loadFeedback(${page-1})" class="page-btn">◀ 上一页</button>`;
            pHtml += `<span class="page-info">${page} / ${data.total_pages}</span>`;
            if (page < data.total_pages) pHtml += `<button onclick="window.app.loadFeedback(${page+1})" class="page-btn">下一页 ▶</button>`;
            pagEl.innerHTML = pHtml;
        } else {
            pagEl.style.display = 'none';
        }
    }

    async fetchAPI(url) {
        const res = await fetch(url);
        const result = await res.json();
        if (result.code !== 200) throw new Error(result.message);
        return result.data;
    }

    renderStatCards(data) {
        const c = document.getElementById('publicStatsCards');
        if (!c || !data) return;

        c.innerHTML = `
            <div class="pub-stat-card card-total">
                <div class="pub-stat-icon">👥</div>
                <div class="pub-stat-info"><div class="pub-stat-value" data-target="${data.total_submissions}">0</div><div class="pub-stat-label">总提交数</div></div>
            </div>
            <div class="pub-stat-card card-refund">
                <div class="pub-stat-icon">💰</div>
                <div class="pub-stat-info"><div class="pub-stat-value">¥${(data.total_refund||0).toLocaleString()}</div><div class="pub-stat-label">总退款金额</div></div>
                <div class="pub-stat-extra">${data.total_users||0}人申请</div>
            </div>`;
        setTimeout(() => this.animateNumbers(), 100);
    }

    animateNumbers() {
        document.querySelectorAll('.pub-stat-value[data-target]').forEach(el => {
            const target = parseInt(el.dataset.target), start = performance.now();
            const update = now => {
                const p = Math.min((now-start)/1500,1), ease=1-Math.pow(1-p,4);
                el.textContent = Math.floor(target*ease).toLocaleString();
                if(p<1) requestAnimationFrame(update); else el.textContent=target.toLocaleString();
            };
            requestAnimationFrame(update);
        });
    }
}

function showRegisterForm() {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('register-form').style.display = 'block';
}
function showLoginForm() {
    document.getElementById('register-form').style.display = 'none';
    document.getElementById('login-form').style.display = 'block';
}
function handleLogout() { fetch('/api/logout.php', {method:'POST'}).finally(()=>location.reload()); }
function refreshPublicStats() { if(window.app) window.app.loadPublicStats(); }
function removeImage() {
    if (!window.app) return;
    window.app.uploadedImageFile = null;
    document.getElementById('evidence-image').value = '';
    document.getElementById('image-placeholder').style.display = 'block';
    document.getElementById('image-preview-wrap').style.display = 'none';
    document.getElementById('image-preview-img').src = '';
}
function viewEvidence(userId) {
    window.open('/api/view_evidence.php?id=' + userId, '_blank', 'width=800,height=600');
}
function downloadMyData(btn) {
    window.location.href = '/api/download_my_data.php';
}

document.addEventListener('DOMContentLoaded', () => { window.app = new App(); });
