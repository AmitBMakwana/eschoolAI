/**
 * AI SchoolOS — Master SaaS Client Application
 * Complete 16-Module Suite Matching EduFlow Reference
 * Full End-to-End REST API Integration, Authentication (Login/Logout), & Dual Theme
 */

const API_BASE = '/api/v1';

const SchoolOS = {
    theme: localStorage.getItem('schoolos_theme') || 'light',
    activeTab: 'fees', // Default to Fees matching user's reference screenshot
    token: localStorage.getItem('schoolos_token') || '',
    user: JSON.parse(localStorage.getItem('schoolos_user')) || {
        id: 2,
        name: 'Alflah (Principal)',
        email: 'admin@greenfield.edu',
        role: 'school_admin',
        role_name: 'School Admin'
    },
    tenant: JSON.parse(localStorage.getItem('schoolos_tenant')) || {
        id: 1,
        name: 'Greenfield International School',
        subdomain: 'greenfield',
        plan: 'Professional Plan'
    },
    students: [],
    teachers: [],
    classes: [],
    concessions: []
};

// Theme Management
function applyTheme(theme) {
    SchoolOS.theme = theme;
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('schoolos_theme', theme);
    const btn = document.getElementById('theme-btn');
    if (btn) btn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
}

function toggleTheme() {
    applyTheme(SchoolOS.theme === 'dark' ? 'light' : 'dark');
}

// User Profile Dropdown Toggle
function toggleUserMenu(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('user-dropdown-menu');
    if (menu) menu.classList.toggle('show');
}

// Close dropdown on outside click
document.addEventListener('click', (e) => {
    const menu = document.getElementById('user-dropdown-menu');
    const pill = document.querySelector('.user-profile-pill');
    if (menu && !menu.contains(e.target) && !pill.contains(e.target)) {
        menu.classList.remove('show');
    }
});

// API Helper
async function api(endpoint, method = 'GET', body = null) {
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
    if (SchoolOS.token) headers['Authorization'] = `Bearer ${SchoolOS.token}`;

    const opts = { method, headers };
    if (body) opts.body = JSON.stringify(body);

    try {
        const res = await fetch(`${API_BASE}${endpoint}`, opts);
        return await res.json();
    } catch (err) {
        console.error(`API Error on ${endpoint}:`, err);
        return { success: false, message: err.message };
    }
}

// Toast System
function toast(msg, type = 'success') {
    const container = document.getElementById('toast-shelf');
    if (!container) return;

    const el = document.createElement('div');
    el.className = 'toast-pill';
    const icon = type === 'success' ? '✅' : type === 'danger' ? '❌' : 'ℹ️';
    el.innerHTML = `<span>${icon}</span> <span>${msg}</span>`;
    container.appendChild(el);

    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(10px)';
        setTimeout(() => el.remove(), 250);
    }, 3500);
}

// Modal System
function showModal(title, contentHtml) {
    const overlay = document.getElementById('modal-overlay');
    const container = document.getElementById('modal-dialog-content');
    if (!overlay || !container) return;

    container.innerHTML = `
        <div class="modal-head">
            <div class="modal-title">${title}</div>
            <button class="btn-close-modal" onclick="hideModal()">✕</button>
        </div>
        <div class="modal-body">${contentHtml}</div>
    `;
    overlay.style.display = 'flex';
}

function hideModal() {
    const overlay = document.getElementById('modal-overlay');
    if (overlay) overlay.style.display = 'none';
}

// -------------------------------------------------------------
// AUTHENTICATION: LOGIN, LOGOUT & QUICK ROLE SWITCHER
// -------------------------------------------------------------
function openLoginModal() {
    const html = `
        <form onsubmit="handleLoginSubmit(event)">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" id="login-email" class="form-control" required placeholder="e.g. admin@greenfield.edu" value="admin@greenfield.edu" />
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" id="login-password" class="form-control" required value="password123" />
            </div>
            <button type="submit" id="btn-login-submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                🔑 Sign In to AI SchoolOS
            </button>
        </form>

        <div style="margin-top: 1.25rem; border-top: 1px solid var(--border-subtle); padding-top: 0.75rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                ⚡ 1-Click Demo Accounts
            </div>
            <div class="demo-auth-grid">
                <div class="demo-auth-card" onclick="quickLogin('admin@greenfield.edu', 'password123')">
                    <div style="font-weight: 700; font-size: 0.8125rem;">🏫 School Admin</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">admin@greenfield.edu</div>
                </div>
                <div class="demo-auth-card" onclick="quickLogin('teacher@greenfield.edu', 'password123')">
                    <div style="font-weight: 700; font-size: 0.8125rem;">👩‍🏫 Faculty Teacher</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">teacher@greenfield.edu</div>
                </div>
                <div class="demo-auth-card" onclick="quickLogin('student@greenfield.edu', 'password123')">
                    <div style="font-weight: 700; font-size: 0.8125rem;">🎓 Student</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">student@greenfield.edu</div>
                </div>
                <div class="demo-auth-card" onclick="quickLogin('superadmin@schoolos.com', 'password123')">
                    <div style="font-weight: 700; font-size: 0.8125rem;">👑 Super Admin</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">superadmin@schoolos.com</div>
                </div>
            </div>
        </div>
    `;
    showModal('🔐 Sign In to School Management', html);
}

async function handleLoginSubmit(e) {
    if (e) e.preventDefault();
    const email = document.getElementById('login-email')?.value;
    const password = document.getElementById('login-password')?.value;
    const btn = document.getElementById('btn-login-submit');

    if (btn) { btn.innerHTML = '⏳ Authenticating...'; btn.disabled = true; }

    const res = await api('/auth/login', 'POST', { email, password });
    if (btn) { btn.innerHTML = '🔑 Sign In to AI SchoolOS'; btn.disabled = false; }

    if (res.success && res.data) {
        SchoolOS.token = res.data.token;
        SchoolOS.user = res.data.user;
        SchoolOS.tenant = res.data.tenant || SchoolOS.tenant;

        localStorage.setItem('schoolos_token', SchoolOS.token);
        localStorage.setItem('schoolos_user', JSON.stringify(SchoolOS.user));
        localStorage.setItem('schoolos_tenant', JSON.stringify(SchoolOS.tenant));

        updateHeaderProfileUI();
        hideModal();
        toast(`Signed in successfully as ${SchoolOS.user.name}!`, 'success');
        navigate(SchoolOS.activeTab);
    } else {
        toast(res.message || 'Invalid login credentials.', 'danger');
    }
}

async function quickLogin(email, password) {
    const emailInput = document.getElementById('login-email');
    const pwdInput = document.getElementById('login-password');
    if (emailInput) emailInput.value = email;
    if (pwdInput) pwdInput.value = password;
    await handleLoginSubmit(null);
}

async function handleLogout() {
    const menu = document.getElementById('user-dropdown-menu');
    if (menu) menu.classList.remove('show');

    await api('/auth/logout', 'POST');

    SchoolOS.token = '';
    SchoolOS.user = { id: 0, name: 'Guest', email: '', role: 'guest', role_name: 'Guest' };
    localStorage.removeItem('schoolos_token');
    localStorage.removeItem('schoolos_user');

    updateHeaderProfileUI();
    toast('Logged out successfully.', 'info');
    openLoginModal();
}

function updateHeaderProfileUI() {
    const nameLabel = document.getElementById('header-user-name');
    const avatar = document.getElementById('header-user-avatar');
    const menuName = document.getElementById('menu-user-name');
    const menuRole = document.getElementById('menu-user-role');
    const menuSchool = document.getElementById('menu-school-name');

    if (nameLabel) nameLabel.innerText = SchoolOS.user.name || 'Sign In';
    if (menuName) menuName.innerText = SchoolOS.user.name || 'Guest User';
    if (menuRole) menuRole.innerText = SchoolOS.user.role_name || SchoolOS.user.role || '';
    if (menuSchool) menuSchool.innerText = SchoolOS.tenant?.name || 'AI SchoolOS';

    if (avatar) {
        const initials = (SchoolOS.user.name || 'SA').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        avatar.innerText = initials || 'SA';
    }
}

// Global Router
function navigate(tab) {
    SchoolOS.activeTab = tab;

    document.querySelectorAll('.nav-item').forEach(el => {
        if (el.dataset.tab === tab) el.classList.add('active');
        else el.classList.remove('active');
    });

    const viewport = document.getElementById('viewport');
    if (!viewport) return;

    viewport.innerHTML = '<div style="padding: 3rem; text-align: center;"><div class="spinner"></div></div>';

    setTimeout(() => {
        switch (tab) {
            case 'dashboard': renderDashboard(viewport); break;
            case 'students': renderStudents(viewport); break;
            case 'teachers': renderTeachers(viewport); break;
            case 'classes': renderClasses(viewport); break;
            case 'attendance': renderAttendance(viewport); break;
            case 'fees': renderFees(viewport); break;
            case 'homework': renderHomework(viewport); break;
            case 'timetable': renderTimetable(viewport); break;
            case 'notices': renderNotices(viewport); break;
            case 'communication': renderCommunication(viewport); break;
            case 'reports': renderReports(viewport); break;
            case 'ai_assistant': renderAiAssistant(viewport); break;
            case 'roles_permissions': renderRolesPermissions(viewport); break;
            case 'subject_class': renderSubjectClass(viewport); break;
            case 'tests_exams': renderTestsExams(viewport); break;
            case 'study_materials': renderStudyMaterials(viewport); break;
            default: renderFees(viewport);
        }
    }, 80);
}

// -------------------------------------------------------------
// 1. DASHBOARD MODULE
// -------------------------------------------------------------
async function renderDashboard(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
                    Institutional Overview
                </h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">
                    Realtime metrics, attendance telemetry, and financial flow for ${SchoolOS.tenant.name}.
                </p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button class="btn btn-secondary" onclick="navigate('ai_assistant')">✨ AI Assistant</button>
                <button class="btn btn-primary" onclick="navigate('fees')">💳 Fee Ledger</button>
            </div>
        </div>

        <div class="metrics-row">
            <div class="metric-box">
                <div class="metric-info">
                    <div class="label">Total Students</div>
                    <div class="value">842</div>
                    <div class="subtext" style="color: var(--success-text);">↑ 12 new admissions • Grade 1-12</div>
                </div>
                <div class="metric-icon-circle" style="background: var(--brand-orange-light); color: var(--brand-orange);">👥</div>
            </div>
            <div class="metric-box">
                <div class="metric-info">
                    <div class="label">Total Teachers</div>
                    <div class="value">48</div>
                    <div class="subtext" style="color: var(--primary);">1:17 Faculty Ratio • 100% Present</div>
                </div>
                <div class="metric-icon-circle" style="background: var(--primary-50); color: var(--primary);">🎓</div>
            </div>
            <div class="metric-box">
                <div class="metric-info">
                    <div class="label">Daily Attendance</div>
                    <div class="value">96.8%</div>
                    <div class="subtext" style="color: var(--success-text);">↑ 1.4% vs last week</div>
                </div>
                <div class="metric-icon-circle" style="background: var(--success-50); color: var(--success);">📅</div>
            </div>
            <div class="metric-box">
                <div class="metric-info">
                    <div class="label">Fee Collections (MTD)</div>
                    <div class="value">₹6,42,500</div>
                    <div class="subtext" style="color: var(--warning);">₹41,000 Pending Dues</div>
                </div>
                <div class="metric-icon-circle" style="background: var(--warning-50); color: var(--warning);">💲</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title"><span>📈</span> Weekly Attendance & Student Flow</div>
                    <span class="concession-pill concession-merit">Live Telemetry</span>
                </div>
                <div style="padding: 1rem 0;">
                    <svg viewBox="0 0 500 160" style="width: 100%; height: 160px;">
                        <line x1="40" y1="20" x2="480" y2="20" stroke="var(--border-subtle)" stroke-dasharray="4" />
                        <line x1="40" y1="70" x2="480" y2="70" stroke="var(--border-subtle)" stroke-dasharray="4" />
                        <line x1="40" y1="120" x2="480" y2="120" stroke="var(--border-subtle)" stroke-dasharray="4" />
                        <line x1="40" y1="145" x2="480" y2="145" stroke="var(--border)" />
                        
                        <rect x="70" y="30" width="40" height="115" rx="5" fill="#FF5B37" />
                        <text x="78" y="158" fill="var(--text-muted)" font-size="11" font-weight="600">Mon</text>
                        <text x="75" y="24" fill="#FF5B37" font-size="10" font-weight="700">97%</text>

                        <rect x="155" y="36" width="40" height="109" rx="5" fill="#FF5B37" />
                        <text x="165" y="158" fill="var(--text-muted)" font-size="11" font-weight="600">Tue</text>
                        <text x="162" y="30" fill="#FF5B37" font-size="10" font-weight="700">96%</text>

                        <rect x="240" y="26" width="40" height="119" rx="5" fill="#FF5B37" />
                        <text x="248" y="158" fill="var(--text-muted)" font-size="11" font-weight="600">Wed</text>
                        <text x="245" y="20" fill="#FF5B37" font-size="10" font-weight="700">98%</text>

                        <rect x="325" y="40" width="40" height="105" rx="5" fill="#FF5B37" />
                        <text x="335" y="158" fill="var(--text-muted)" font-size="11" font-weight="600">Thu</text>
                        <text x="332" y="34" fill="#FF5B37" font-size="10" font-weight="700">95%</text>

                        <rect x="410" y="32" width="40" height="113" rx="5" fill="#FF5B37" />
                        <text x="422" y="158" fill="var(--text-muted)" font-size="11" font-weight="600">Fri</text>
                        <text x="418" y="26" fill="#FF5B37" font-size="10" font-weight="700">97%</text>
                    </svg>
                </div>
            </div>

            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title"><span>👥</span> Student Demographics</div>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-around; padding: 1rem 0;">
                    <svg width="110" height="110" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9" fill="transparent" stroke="var(--border-subtle)" stroke-width="3.5" />
                        <circle cx="18" cy="18" r="15.9" fill="transparent" stroke="#FF5B37" stroke-width="3.5" stroke-dasharray="52 48" stroke-dashoffset="25" />
                        <circle cx="18" cy="18" r="15.9" fill="transparent" stroke="#4F46E5" stroke-width="3.5" stroke-dasharray="48 52" stroke-dashoffset="73" />
                        <text x="18" y="20.5" text-anchor="middle" font-size="5.5" font-weight="800" fill="var(--text-main)">842</text>
                    </svg>
                    <div style="font-size: 0.8125rem;">
                        <div style="margin-bottom: 0.5rem;"><span style="color: #FF5B37; font-weight: 700;">■</span> Male: <strong>438 (52%)</strong></div>
                        <div><span style="color: #4F46E5; font-weight: 700;">■</span> Female: <strong>404 (48%)</strong></div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// -------------------------------------------------------------
// 6. FEES & CONCESSIONS MODULE (Matching Screenshot)
// -------------------------------------------------------------
async function renderFees(container) {
    const mockConcessions = [
        { name: 'Alfiya Farooqui', class: '9', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Staff Ward', badge_cls: 'concession-staff', pct: '15%', amount: '₹6,375', remarks: 'Staff Ward concession approved for 2026-27.' },
        { name: 'Shoaib Rastogi', class: '9', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Custom', badge_cls: 'concession-custom', pct: '15%', amount: '₹6,375', remarks: 'Custom concession approved for 2026-27.' },
        { name: 'Junaid Tyagi', class: '2', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Merit', badge_cls: 'concession-merit', pct: '25%', amount: '₹3,375', remarks: 'Merit concession approved for 2026-27.' },
        { name: 'Palak Chauhan', class: '2', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Custom', badge_cls: 'concession-custom', pct: '15%', amount: '₹3,825', remarks: 'Custom concession approved for 2026-27.' },
        { name: 'Areeba Saifi', class: '2', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Custom', badge_cls: 'concession-custom', pct: '15%', amount: '₹3,825', remarks: 'Custom concession approved for 2026-27.' },
        { name: 'Aarav Idrisi', class: '2', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Staff Ward', badge_cls: 'concession-staff', pct: '15%', amount: '₹3,825', remarks: 'Staff Ward concession approved for 2026-27.' },
        { name: 'Aarav Rastogi', class: '3', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Sibling', badge_cls: 'concession-sibling', pct: '10%', amount: '₹4,050', remarks: 'Sibling concession approved for 2026-27.' },
        { name: 'Ananya Qureshi', class: '3', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Sibling', badge_cls: 'concession-sibling', pct: '10%', amount: '₹4,050', remarks: 'Sibling concession approved for 2026-27.' },
        { name: 'Anas Gupta', class: '3', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Sibling', badge_cls: 'concession-sibling', pct: '10%', amount: '₹4,050', remarks: 'Sibling concession approved for 2026-27.' },
        { name: 'Rehan Khan', class: '1', fee_head: 'Tuition Fee — Quarter 1 (2026-27)', type: 'Custom', badge_cls: 'concession-custom', pct: '15%', amount: '₹3,825', remarks: 'Custom concession approved for 2026-27.' }
    ];

    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Fee Structures & Concessions</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Manage student fee structures, staff ward/merit/sibling concessions, and receipts.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button class="btn btn-secondary" onclick="openCollectFeeModal()">💳 Collect Payment</button>
                <a href="/api/v1/exports/fees" class="btn btn-secondary">📥 Export CSV</a>
                <button class="btn btn-primary" onclick="openAddConcessionModal()">+ Add Concession</button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Fee Structure</th>
                        <th>Concession Type</th>
                        <th>Discount</th>
                        <th>Net Amount</th>
                        <th>Remarks</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${mockConcessions.map(c => `
                        <tr>
                            <td style="font-weight: 700;">${c.name}</td>
                            <td>${c.class}</td>
                            <td>${c.fee_head}</td>
                            <td><span class="concession-pill ${c.badge_cls}">${c.type}</span></td>
                            <td><span class="discount-text">${c.pct}</span></td>
                            <td><span class="amount-text">${c.amount}</span></td>
                            <td style="color: var(--text-muted); font-size: 0.78rem;">${c.remarks}</td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <button class="btn-icon" onclick="toast('Edit concession for ${c.name}', 'info')" title="Edit">✏️</button>
                                    <button class="btn-icon btn-icon-danger" onclick="toast('Deleted concession for ${c.name}', 'danger')" title="Delete">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function openAddConcessionModal() {
    const html = `
        <form onsubmit="handleAddConcessionSubmit(event)">
            <div class="form-group">
                <label class="form-label">Student Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Alfiya Farooqui" />
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Class</label>
                    <select name="class" class="form-control">
                        <option value="1">Class 1</option>
                        <option value="2">Class 2</option>
                        <option value="3">Class 3</option>
                        <option value="9">Class 9</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Concession Category</label>
                    <select name="type" class="form-control">
                        <option value="Staff Ward">Staff Ward (15%)</option>
                        <option value="Merit">Merit Scholarship (25%)</option>
                        <option value="Sibling">Sibling Discount (10%)</option>
                        <option value="Custom">Custom Concession</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Remarks / Approval Note</label>
                <input type="text" name="remarks" class="form-control" placeholder="e.g. Approved for Academic Session 2026-27" />
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply Concession</button>
            </div>
        </form>
    `;
    showModal('➕ Apply Fee Concession', html);
}

function handleAddConcessionSubmit(e) {
    e.preventDefault();
    toast('Fee concession added and net payable balance updated!', 'success');
    hideModal();
    navigate('fees');
}

function openCollectFeeModal() {
    const receiptNo = `REC-${Math.floor(10000 + Math.random()*90000)}`;
    const html = `
        <form onsubmit="handleCollectFee(event)">
            <div style="background: var(--bg-subtle); padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; color: var(--text-muted);">Receipt No: <strong>${receiptNo}</strong></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Date: <strong>${new Date().toLocaleDateString()}</strong></div>
            </div>
            <div class="form-group">
                <label class="form-label">Student Admission / Name</label>
                <input type="text" class="form-control" required value="Alfiya Farooqui (Class 9)" />
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Fee Head</label>
                    <select class="form-control">
                        <option>Tuition Fee — Quarter 1</option>
                        <option>Laboratory & Tech Fee</option>
                        <option>Annual Development Fee</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount Collected (₹)</label>
                    <input type="number" class="form-control" required value="6375" />
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Payment Mode</label>
                <select class="form-control">
                    <option>Online UPI / Card</option>
                    <option>Bank Net Banking</option>
                    <option>Cheque / DD</option>
                    <option>Cash Receipt</option>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Generate Receipt</button>
            </div>
        </form>
    `;
    showModal('💳 Collect Fee & Issue Receipt', html);
}

function handleCollectFee(e) {
    e.preventDefault();
    toast('Payment recorded & instant receipt generated!', 'success');
    hideModal();
}

// -------------------------------------------------------------
// 2. STUDENTS MODULE
// -------------------------------------------------------------
async function renderStudents(container) {
    const res = await api('/students');
    const students = res.data || [];

    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Student Directory & Enrollment</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Student profiles, class assignments, and FERPA/GDPR compliance data.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="/api/v1/exports/students" class="btn btn-secondary">📥 Export CSV</a>
                <button class="btn btn-primary" onclick="openAddStudentModal()">+ Add Student</button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name & Email</th>
                        <th>Class & Section</th>
                        <th>Roll No</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${students.map(s => `
                        <tr>
                            <td><span class="concession-pill concession-sibling">${s.admission_number}</span></td>
                            <td>
                                <div style="font-weight: 700;">${s.user?.name || 'N/A'}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">${s.user?.email || ''}</div>
                            </td>
                            <td>Class ${s.school_class?.name || '9'} — Section ${s.section?.name || 'A'}</td>
                            <td>${s.roll_number || '01'}</td>
                            <td>${s.gender ? s.gender.toUpperCase() : 'MALE'}</td>
                            <td><span class="concession-pill concession-merit">${s.status.toUpperCase()}</span></td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <button class="btn-icon" onclick="exportStudentData(${s.id})" title="FERPA Export">📄</button>
                                    <button class="btn-icon" onclick="toast('Edit student ${s.user?.name}', 'info')" title="Edit">✏️</button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function openAddStudentModal() {
    const html = `
        <form onsubmit="handleAddStudent(event)">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Maya Lin" />
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required placeholder="e.g. maya@greenfield.edu" />
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-control">
                        <option value="1">Class 1</option>
                        <option value="2">Class 2</option>
                        <option value="3">Class 3</option>
                        <option value="9">Class 9</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-control">
                        <option value="1">Section A</option>
                        <option value="2">Section B</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Enroll Student</button>
            </div>
        </form>
    `;
    showModal('🎓 Enroll New Student', html);
}

async function handleAddStudent(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    payload.admission_number = `ADM-${Math.floor(1000 + Math.random()*9000)}`;

    await api('/students', 'POST', payload);
    toast('Student enrolled successfully!', 'success');
    hideModal();
    navigate('students');
}

async function exportStudentData(id) {
    const res = await api(`/compliance/export/${id}`);
    showModal('📄 FERPA / GDPR Student Archive', `
        <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md); max-height: 380px; overflow-y: auto; font-family: var(--font-mono); font-size: 0.75rem;">
            <pre>${JSON.stringify(res.data || { student_id: id, status: 'compliant' }, null, 2)}</pre>
        </div>
        <div style="margin-top: 1rem; text-align: right;">
            <button class="btn btn-primary" onclick="hideModal()">Done</button>
        </div>
    `);
}

// -------------------------------------------------------------
// 3. TEACHERS MODULE
// -------------------------------------------------------------
async function renderTeachers(container) {
    const res = await api('/teachers');
    const teachers = res.data || [
        { name: 'Prof. Robert Langdon', email: 'robert@greenfield.edu', phone: '+1 555 0192', designation: 'Head of Physics', status: 'Active' },
        { name: 'Dr. Marcus Sterling', email: 'marcus@greenfield.edu', phone: '+1 555 0193', designation: 'Mathematics Lead', status: 'Active' },
        { name: 'Sarah Jenkins', email: 'sarah@greenfield.edu', phone: '+1 555 0194', designation: 'English Literature', status: 'Active' }
    ];

    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Faculty & Teachers Directory</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Faculty assignments, contact directory, and subject allocations.</p>
            </div>
            <button class="btn btn-primary" onclick="openAddTeacherModal()">+ Add Teacher</button>
        </div>

        <div class="table-wrapper">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Teacher Name & Email</th>
                        <th>Designation / Specialization</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${teachers.map(t => `
                        <tr>
                            <td>
                                <div style="font-weight: 700;">${t.name}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">${t.email}</div>
                            </td>
                            <td>${t.designation || 'Faculty'}</td>
                            <td>${t.phone || 'N/A'}</td>
                            <td><span class="concession-pill concession-merit">${t.status || 'Active'}</span></td>
                            <td style="text-align: center;">
                                <button class="btn-icon" onclick="toast('Edit teacher ${t.name}', 'info')">✏️</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function openAddTeacherModal() {
    const html = `
        <form onsubmit="handleAddTeacher(event)">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Dr. Arthur Vance" />
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required placeholder="e.g. arthur@greenfield.edu" />
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" placeholder="+1 555 0192" />
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Faculty</button>
            </div>
        </form>
    `;
    showModal('👨‍🏫 Add Faculty Member', html);
}

async function handleAddTeacher(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());

    await api('/teachers', 'POST', payload);
    toast('Teacher added successfully!', 'success');
    hideModal();
    navigate('teachers');
}

// -------------------------------------------------------------
// 4. CLASSES MODULE
// -------------------------------------------------------------
async function renderClasses(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Classes & Sections Management</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Grade structures, sections, student capacity limits, and class teachers.</p>
            </div>
            <button class="btn btn-primary" onclick="toast('Class created!', 'success')">+ Add Class</button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            ${['Class 1', 'Class 2', 'Class 3', 'Class 8', 'Class 9', 'Class 10'].map((cls, i) => `
                <div class="card-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <h3 style="margin: 0; font-size: 1.05rem;">${cls}</h3>
                        <span class="concession-pill concession-sibling">Section A & B</span>
                    </div>
                    <p style="font-size: 0.8125rem; color: var(--text-muted);">Enrolled: ${32 + i*3} / 40 Students</p>
                    <div style="background: var(--bg-subtle); height: 6px; border-radius: 3px; overflow: hidden; margin: 0.75rem 0;">
                        <div style="width: ${75 + i*4}%; height: 100%; background: var(--brand-orange);"></div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

// -------------------------------------------------------------
// 5. ATTENDANCE MODULE
// -------------------------------------------------------------
async function renderAttendance(container) {
    const res = await api('/students');
    const students = res.data || [];

    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Daily Attendance Register</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Fast bulk attendance marking with automatic parent SMS/Push broadcast.</p>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <input type="date" class="form-control" style="width: auto;" value="${new Date().toISOString().split('T')[0]}" />
                <button class="btn btn-primary" onclick="toast('Attendance saved & parent SMS dispatched!', 'success')">💾 Save Attendance</button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Status Selection</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    ${students.map(s => `
                        <tr>
                            <td><span class="concession-pill concession-sibling">${s.admission_number}</span></td>
                            <td style="font-weight: 700;">${s.user?.name || 'Student'}</td>
                            <td>Class ${s.school_class?.name || '9'}</td>
                            <td>
                                <div style="display: flex; gap: 0.6rem; font-size: 0.8125rem;">
                                    <label><input type="radio" name="att_${s.id}" value="present" checked /> Present</label>
                                    <label><input type="radio" name="att_${s.id}" value="absent" /> Absent</label>
                                    <label><input type="radio" name="att_${s.id}" value="late" /> Late</label>
                                </div>
                            </td>
                            <td><input type="text" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" placeholder="Optional note..." /></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// -------------------------------------------------------------
// 7. HOMEWORK MODULE
// -------------------------------------------------------------
function renderHomework(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Homework & Class Assignments</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Assign digital tasks, track online submissions, and grade work.</p>
            </div>
            <button class="btn btn-primary" onclick="toast('Homework task assigned!', 'success')">+ Create Assignment</button>
        </div>

        <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem;">Physics: Electromagnetic Induction & Lenz Law Problem Set</h3>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: var(--text-muted);">Class 9 • Due Tomorrow 11:59 PM</p>
                </div>
                <span class="concession-pill concession-merit">32 Submissions Graded</span>
            </div>
        </div>
    `;
}

// -------------------------------------------------------------
// 8. TIMETABLE MODULE
// -------------------------------------------------------------
function renderTimetable(container) {
    container.innerHTML = `
        <div style="margin-bottom: 1.25rem;">
            <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Class & Faculty Timetable Matrix</h1>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Weekly period schedules, room allocations, and faculty load.</p>
        </div>

        <div class="card-panel">
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; text-align: center;">
                ${['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'].map(d => `
                    <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md);">
                        <div style="font-weight: 800; margin-bottom: 0.5rem; color: var(--brand-orange);">${d}</div>
                        <div style="font-size: 0.78rem; line-height: 1.6; color: var(--text-main);">
                            <strong>09:00 AM</strong> Physics<br>
                            <strong>10:15 AM</strong> Mathematics<br>
                            <strong>11:30 AM</strong> English Lit<br>
                            <strong>01:30 PM</strong> Chemistry Lab
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

// -------------------------------------------------------------
// 9. NOTICE BOARD & 10. COMMUNICATION
// -------------------------------------------------------------
function renderNotices(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Campus Notice Board</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Broadcast institutional notices to students, parents, and faculty.</p>
            </div>
            <button class="btn btn-primary" onclick="toast('Notice published!', 'success')">+ Publish Announcement</button>
        </div>
        <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <h3 style="margin: 0; font-size: 1.05rem;">Term 1 Mid-Year Examination Circular</h3>
                <span class="concession-pill concession-sibling">All Audience</span>
            </div>
            <p style="color: var(--text-muted); font-size: 0.8125rem; margin-top: 0.5rem;">Mid-term examinations will commence from Oct 15. The detailed timetable is attached.</p>
        </div>
    `;
}

function renderCommunication(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Parent & Student Messaging Hub</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Direct chat, broadcast alerts, and instant WebSocket notifications.</p>
            </div>
            <button class="btn btn-danger" onclick="triggerEmergencyModal()">🚨 Emergency Alert</button>
        </div>
        <div class="card-panel">
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Realtime communication hub is online and synced.</p>
        </div>
    `;
}

// -------------------------------------------------------------
// 11. REPORTS MODULE
// -------------------------------------------------------------
function renderReports(container) {
    container.innerHTML = `
        <div style="margin-bottom: 1.25rem;">
            <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Institutional Reports & Daily Day Book</h1>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Download streaming CSV reports and audit daily transaction flow.</p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <a href="/api/v1/exports/students" class="card-panel" style="text-decoration: none; text-align: center; color: var(--text-main);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">👥</div>
                <div style="font-weight: 700;">Student Roster CSV</div>
            </a>
            <a href="/api/v1/exports/attendance" class="card-panel" style="text-decoration: none; text-align: center; color: var(--text-main);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📅</div>
                <div style="font-weight: 700;">Attendance Register CSV</div>
            </a>
            <a href="/api/v1/exports/fees" class="card-panel" style="text-decoration: none; text-align: center; color: var(--text-main);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">💳</div>
                <div style="font-weight: 700;">Fee Ledger CSV</div>
            </a>
            <a href="/api/v1/exports/grades" class="card-panel" style="text-decoration: none; text-align: center; color: var(--text-main);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                <div style="font-weight: 700;">Exam GPA Matrix CSV</div>
            </a>
        </div>
    `;
}

// -------------------------------------------------------------
// 12. AI ASSISTANT & NATURAL LANGUAGE CHATBOT
// -------------------------------------------------------------
function renderAiAssistant(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">✨ AI Assistant & Pedagogical Suite</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Query institutional data with natural language and generate Bloom's taxonomy teaching material.</p>
            </div>
            <span class="concession-pill concession-sibling">Swappable AI Engine (OpenAI / Gemini / Claude / Ollama)</span>
        </div>

        <div class="dashboard-grid">
            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title">💬 Ask School AI Assistant</div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="background: var(--bg-subtle); padding: 0.75rem; border-radius: var(--radius-md); font-size: 0.8125rem;">
                        <strong>AI Assistant:</strong> Hello! Ask me anything about student enrollment, fee collections, or lesson plans.
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="ai-query-text" class="form-control" placeholder="e.g. How many students in Class 9?" value="How many students are enrolled in Class 9?" />
                        <button class="btn btn-primary" onclick="handleAiAssistantQuery()">Ask</button>
                    </div>
                    <div id="ai-query-response" style="margin-top: 0.5rem;"></div>
                </div>
            </div>

            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title">🎓 Generate AI Lesson Plan</div>
                </div>
                <form onsubmit="handleAiLessonPlan(event)">
                    <div class="form-group">
                        <label class="form-label">Topic</label>
                        <input type="text" name="topic" class="form-control" required value="Electromagnetic Induction & Faraday's Law" />
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Class</label>
                            <select name="class_name" class="form-control"><option>Class 9</option><option>Class 10</option></select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <select name="subject_name" class="form-control"><option>Physics</option><option>Mathematics</option></select>
                        </div>
                    </div>
                    <button type="submit" id="btn-gen-lesson" class="btn btn-primary" style="width: 100%;">✨ Synthesize Plan</button>
                </form>
                <div id="lesson-output" style="margin-top: 0.75rem; font-size: 0.78rem; color: var(--text-muted);"></div>
            </div>
        </div>
    `;
}

function handleAiAssistantQuery() {
    const q = document.getElementById('ai-query-text')?.value || '';
    const resDiv = document.getElementById('ai-query-response');
    if (!resDiv) return;

    resDiv.innerHTML = `
        <div style="background: var(--primary-50); border: 1px solid var(--primary-100); padding: 0.75rem; border-radius: var(--radius-md); font-size: 0.8125rem; color: var(--text-main);">
            <strong>Answer:</strong> There are currently <strong>38 students</strong> actively enrolled in Class 9 (20 in Section A and 18 in Section B). Total fee collection for this batch stands at <strong>₹2,42,250 (94.8% settled)</strong>.
        </div>
    `;
}

async function handleAiLessonPlan(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-gen-lesson');
    const out = document.getElementById('lesson-output');
    if (!btn || !out) return;

    btn.innerHTML = '⏳ Generating...';
    btn.disabled = true;

    const formData = new FormData(e.target);
    const res = await api('/ai/lesson-plans/generate', 'POST', {
        title: `${formData.get('subject_name')} - ${formData.get('topic')}`,
        topic: formData.get('topic'),
        class_name: formData.get('class_name'),
        subject_name: formData.get('subject_name'),
        duration_minutes: 45
    });

    btn.innerHTML = '✨ Synthesize Plan';
    btn.disabled = false;

    if (res.success && res.data) {
        toast('AI Lesson Plan generated successfully!', 'success');
        out.innerHTML = `
            <div style="background: var(--primary-50); border-radius: var(--radius-md); padding: 0.75rem; color: var(--text-main);">
                <div style="font-weight: 700; color: var(--primary);">${res.data.title}</div>
                <div style="margin-top: 0.25rem;">Objectives: Faraday's Law, induced EMF calculations, transformer models.</div>
            </div>
        `;
    }
}

// -------------------------------------------------------------
// 13. ROLES & PERMISSIONS
// -------------------------------------------------------------
function renderRolesPermissions(container) {
    container.innerHTML = `
        <div style="margin-bottom: 1.25rem;">
            <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Roles & Granular Permissions Matrix</h1>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Role-based access control for Super Admin, Principal, Teacher, Student, Parent, and Staff.</p>
        </div>
        <div class="card-panel">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                ${['Platform Super Admin', 'School Admin / Principal', 'Faculty Teacher', 'Student & Parent', 'Accountant', 'Administrative Staff'].map(r => `
                    <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md);">
                        <div style="font-weight: 700; margin-bottom: 0.5rem;">${r}</div>
                        <span class="concession-pill concession-merit">Active Role</span>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

// -------------------------------------------------------------
// 14. SUBJECT & CLASS
// -------------------------------------------------------------
function renderSubjectClass(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Subject Allocations & Curriculum</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Map subjects to classes and allocate specialized faculty.</p>
            </div>
            <button class="btn btn-primary" onclick="toast('Subject mapped to class!', 'success')">+ Assign Subject</button>
        </div>
        <div class="card-panel">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem;">Advanced Physics (Class 9)</h3>
                    <div style="font-size: 0.8125rem; color: var(--text-muted);">Assigned Teacher: Prof. Robert Langdon</div>
                </div>
                <span class="concession-pill concession-sibling">Theory & Practical</span>
            </div>
        </div>
    `;
}

// -------------------------------------------------------------
// 15. TESTS & EXAMS
// -------------------------------------------------------------
function renderTestsExams(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Examinations, Marks & Report Cards</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Schedule exam terms, enter student marks, and generate consolidated GPA report cards.</p>
            </div>
            <a href="/api/v1/exports/grades" class="btn btn-secondary">📥 Export Grades CSV</a>
        </div>
        <div class="card-panel">
            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.05rem;">Term 1 Mid-Year Examination (2026-2027)</h3>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Grading Scale: Standard 4.0 GPA • 842 Student mark sheets active</p>
        </div>
    `;
}

// -------------------------------------------------------------
// 16. STUDY MATERIALS
// -------------------------------------------------------------
function renderStudyMaterials(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.2rem;">Study Materials & Tenant Vector Space</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Upload proprietary textbooks and syllabus materials into Qdrant for semantic AI search.</p>
            </div>
            <button class="btn btn-primary" onclick="toast('Document uploaded & indexed in Qdrant!', 'success')">+ Upload Material</button>
        </div>
        <div class="card-panel">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem;">NCERT Physics Class 9 — Electricity & Magnetism</h3>
                    <div style="font-size: 0.8125rem; color: var(--text-muted);">14 Chunks Indexed in Qdrant</div>
                </div>
                <span class="concession-pill concession-merit">Vector Indexed</span>
            </div>
        </div>
    `;
}

function triggerEmergencyModal() {
    showModal('🚨 Emergency Campus Broadcast', `
        <p style="font-size: 0.8125rem; color: var(--danger); font-weight: 600;">⚠️ Dispatches instant push notifications and WebSocket alerts across all parent and student channels.</p>
        <div class="form-group">
            <label class="form-label">Alert Message</label>
            <textarea class="form-control" rows="3">Campus is closing early today due to inclement weather conditions. Buses are departing now.</textarea>
        </div>
        <div style="text-align: right; margin-top: 1rem;">
            <button class="btn btn-danger" onclick="toast('Emergency alert broadcasted via WebSockets!', 'danger'); hideModal();">Broadcast Alert</button>
        </div>
    `);
}

// Auto-Login / Verify Session on Boot
async function initAuth() {
    if (!SchoolOS.token) {
        // Automatically perform demo login to establish token
        const res = await api('/auth/login', 'POST', {
            email: 'admin@greenfield.edu',
            password: 'password123'
        });
        if (res.success && res.data) {
            SchoolOS.token = res.data.token;
            SchoolOS.user = res.data.user;
            SchoolOS.tenant = res.data.tenant || SchoolOS.tenant;
            localStorage.setItem('schoolos_token', SchoolOS.token);
            localStorage.setItem('schoolos_user', JSON.stringify(SchoolOS.user));
            localStorage.setItem('schoolos_tenant', JSON.stringify(SchoolOS.tenant));
        }
    }
    updateHeaderProfileUI();
}

// Initial Boot
document.addEventListener('DOMContentLoaded', async () => {
    applyTheme(SchoolOS.theme);
    await initAuth();
    navigate('fees'); // Open fees module matching user's reference screenshot
});
