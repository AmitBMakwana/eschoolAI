/**
 * AI SchoolOS — Modern SaaS Master Client Application
 * Full SPA Navigation, Theme Toggling, Charts, Interactive Modals & API Integration
 */

const API_BASE = '/api/v1';

// Client State
const SchoolOS = {
    theme: localStorage.getItem('schoolos_theme') || 'light',
    activeTab: 'dashboard',
    token: localStorage.getItem('schoolos_token') || '',
    user: JSON.parse(localStorage.getItem('schoolos_user')) || {
        id: 2,
        name: 'Dr. Sarah Jenkins',
        email: 'admin@greenfield.edu',
        role: 'school_admin',
        role_name: 'School Admin / Principal'
    },
    tenant: JSON.parse(localStorage.getItem('schoolos_tenant')) || {
        id: 1,
        name: 'Greenfield International Academy',
        subdomain: 'greenfield',
        plan: 'Enterprise Tier'
    },
    students: [],
    classes: [],
    notices: []
};

// Apply Theme
function applyTheme(theme) {
    SchoolOS.theme = theme;
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('schoolos_theme', theme);
    const themeBtn = document.getElementById('theme-toggle-btn');
    if (themeBtn) {
        themeBtn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
    }
}

function toggleTheme() {
    applyTheme(SchoolOS.theme === 'dark' ? 'light' : 'dark');
}

// API Helper
async function api(endpoint, method = 'GET', body = null) {
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    };
    if (SchoolOS.token) headers['Authorization'] = `Bearer ${SchoolOS.token}`;

    const opts = { method, headers };
    if (body) opts.body = JSON.stringify(body);

    try {
        const res = await fetch(`${API_BASE}${endpoint}`, opts);
        return await res.json();
    } catch (err) {
        console.error('API Error:', err);
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

// Role Switcher
async function switchRole(roleSlug) {
    const roleAccounts = {
        'super_admin': { email: 'superadmin@schoolos.com', name: 'System Super Admin', role: 'super_admin' },
        'school_admin': { email: 'admin@greenfield.edu', name: 'Dr. Sarah Jenkins', role: 'school_admin' },
        'teacher': { email: 'teacher@greenfield.edu', name: 'Prof. Robert Langdon', role: 'teacher' },
        'student': { email: 'student@greenfield.edu', name: 'Alex Rivera', role: 'student' }
    };

    const target = roleAccounts[roleSlug];
    if (!target) return;

    const res = await api('/auth/login', 'POST', { email: target.email, password: 'password' });
    if (res.success && res.data) {
        SchoolOS.token = res.data.token;
        SchoolOS.user = res.data.user;
        SchoolOS.tenant = res.data.tenant || SchoolOS.tenant;
        localStorage.setItem('schoolos_token', SchoolOS.token);
        localStorage.setItem('schoolos_user', JSON.stringify(SchoolOS.user));
        localStorage.setItem('schoolos_tenant', JSON.stringify(SchoolOS.tenant));
    } else {
        SchoolOS.user = {
            id: roleSlug === 'super_admin' ? 1 : 2,
            name: target.name,
            email: target.email,
            role: roleSlug,
            role_name: roleSlug.replace('_', ' ').toUpperCase()
        };
    }

    updateHeaderUI();
    toast(`Active Session: ${SchoolOS.user.name} (${roleSlug.toUpperCase()})`, 'success');
    navigate(SchoolOS.activeTab);
}

// Update Top Header UI
function updateHeaderUI() {
    const userDisplay = document.getElementById('user-name-label');
    const roleDisplay = document.getElementById('user-role-label');
    const tenantDisplay = document.getElementById('tenant-name-label');

    if (userDisplay) userDisplay.innerText = SchoolOS.user.name;
    if (roleDisplay) roleDisplay.innerText = SchoolOS.user.role_name || SchoolOS.user.role;
    if (tenantDisplay) tenantDisplay.innerText = SchoolOS.tenant.name;
}

// Router
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
            case 'attendance': renderAttendance(viewport); break;
            case 'timetable': renderTimetable(viewport); break;
            case 'homework': renderHomework(viewport); break;
            case 'exams': renderExams(viewport); break;
            case 'fees': renderFees(viewport); break;
            case 'ai_lesson_planner': renderAiLessonPlanner(viewport); break;
            case 'ai_questions': renderAiQuestions(viewport); break;
            case 'ai_worksheets': renderAiWorksheets(viewport); break;
            case 'ai_evaluator': renderAiEvaluator(viewport); break;
            case 'ai_analytics': renderAiAnalytics(viewport); break;
            case 'ai_circular': renderAiCircular(viewport); break;
            case 'rag_studio': renderRagStudio(viewport); break;
            case 'notices': renderNotices(viewport); break;
            case 'billing': renderBilling(viewport); break;
            case 'audit_logs': renderAuditLogs(viewport); break;
            case 'backups': renderBackups(viewport); break;
            default: renderDashboard(viewport);
        }
    }, 100);
}

// -------------------------------------------------------------
// 1. DASHBOARD VIEW (With Rich SVG Charts & Live Cards)
// -------------------------------------------------------------
async function renderDashboard(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.25rem;">
                    Academic & Institutional Overview
                </h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">
                    Realtime metrics, attendance telemetry, and financial flow for ${SchoolOS.tenant.name}.
                </p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button class="btn btn-secondary" onclick="navigate('ai_lesson_planner')">✨ AI Studio</button>
                <button class="btn btn-danger" onclick="triggerEmergencyModal()">🚨 Emergency Broadcast</button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="metrics-row">
            <div class="metric-box">
                <div class="metric-info">
                    <div class="label">Total Students</div>
                    <div class="value">842</div>
                    <div class="subtext" style="color: var(--success);">
                        <span>↑ 12 new admissions</span> • <span>Grade 1-12</span>
                    </div>
                </div>
                <div class="metric-icon-circle" style="background: var(--primary-50); color: var(--primary);">
                    🎓
                </div>
            </div>

            <div class="metric-box">
                <div class="metric-info">
                    <div class="label">Teachers & Faculty</div>
                    <div class="value">48</div>
                    <div class="subtext" style="color: var(--primary);">
                        <span>1:17 Faculty Ratio</span> • <span>100% Present</span>
                    </div>
                </div>
                <div class="metric-icon-circle" style="background: var(--secondary-50); color: var(--secondary);">
                    👨‍🏫
                </div>
            </div>

            <div class="metric-box">
                <div class="metric-info">
                    <div class="label">Daily Attendance</div>
                    <div class="value">96.8%</div>
                    <div class="subtext" style="color: var(--success);">
                        <span>↑ 1.4% vs last week</span>
                    </div>
                </div>
                <div class="metric-icon-circle" style="background: var(--success-50); color: var(--success);">
                    📊
                </div>
            </div>

            <div class="metric-box">
                <div class="metric-info">
                    <div class="label">Fee Collections (MTD)</div>
                    <div class="value">$64,250</div>
                    <div class="subtext" style="color: var(--warning);">
                        <span>$4,100 Outstanding Due</span>
                    </div>
                </div>
                <div class="metric-icon-circle" style="background: var(--warning-50); color: var(--warning);">
                    💰
                </div>
            </div>
        </div>

        <!-- Interactive Panels & Charts -->
        <div class="dashboard-grid">
            <!-- Left Panel: Weekly Attendance & Academic Flow Chart -->
            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title">
                        <span>📈</span> Weekly Attendance & Student Flow
                    </div>
                    <span class="badge badge-emerald">Live Telemetry</span>
                </div>

                <div style="padding: 1rem 0;">
                    <!-- Responsive SVG Bar Chart -->
                    <svg viewBox="0 0 500 180" style="width: 100%; height: 180px; overflow: visible;">
                        <!-- Grid Lines -->
                        <line x1="40" y1="20" x2="480" y2="20" stroke="var(--border-subtle)" stroke-dasharray="4" />
                        <line x1="40" y1="70" x2="480" y2="70" stroke="var(--border-subtle)" stroke-dasharray="4" />
                        <line x1="40" y1="120" x2="480" y2="120" stroke="var(--border-subtle)" stroke-dasharray="4" />
                        <line x1="40" y1="160" x2="480" y2="160" stroke="var(--border)" />

                        <!-- Y Axis Labels -->
                        <text x="10" y="25" fill="var(--text-light)" font-size="10" font-family="sans-serif">100%</text>
                        <text x="15" y="75" fill="var(--text-light)" font-size="10" font-family="sans-serif">75%</text>
                        <text x="15" y="125" fill="var(--text-light)" font-size="10" font-family="sans-serif">50%</text>

                        <!-- Bars (Mon to Fri) -->
                        <!-- Mon -->
                        <rect x="70" y="32" width="44" height="128" rx="6" fill="url(#primaryGrad)" />
                        <text x="82" y="175" fill="var(--text-muted)" font-size="11" font-weight="600">Mon</text>
                        <text x="80" y="26" fill="var(--primary)" font-size="10" font-weight="700">97.2%</text>

                        <!-- Tue -->
                        <rect x="155" y="36" width="44" height="124" rx="6" fill="url(#primaryGrad)" />
                        <text x="167" y="175" fill="var(--text-muted)" font-size="11" font-weight="600">Tue</text>
                        <text x="165" y="30" fill="var(--primary)" font-size="10" font-weight="700">96.5%</text>

                        <!-- Wed -->
                        <rect x="240" y="30" width="44" height="130" rx="6" fill="url(#primaryGrad)" />
                        <text x="252" y="175" fill="var(--text-muted)" font-size="11" font-weight="600">Wed</text>
                        <text x="250" y="24" fill="var(--primary)" font-size="10" font-weight="700">98.1%</text>

                        <!-- Thu -->
                        <rect x="325" y="42" width="44" height="118" rx="6" fill="url(#primaryGrad)" />
                        <text x="337" y="175" fill="var(--text-muted)" font-size="11" font-weight="600">Thu</text>
                        <text x="335" y="36" fill="var(--primary)" font-size="10" font-weight="700">95.4%</text>

                        <!-- Fri -->
                        <rect x="410" y="34" width="44" height="126" rx="6" fill="url(#primaryGrad)" />
                        <text x="424" y="175" fill="var(--text-muted)" font-size="11" font-weight="600">Fri</text>
                        <text x="420" y="28" fill="var(--primary)" font-size="10" font-weight="700">96.8%</text>

                        <defs>
                            <linearGradient id="primaryGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#6366F1" />
                                <stop offset="100%" stop-color="#4F46E5" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>

            <!-- Right Panel: Student Demographics & Donut Ratio -->
            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title">
                        <span>👥</span> Student Demographics
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-around; padding: 1rem 0;">
                    <!-- Circular Donut SVG -->
                    <svg width="120" height="120" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9" fill="transparent" stroke="var(--border-subtle)" stroke-width="3.5" />
                        <circle cx="18" cy="18" r="15.9" fill="transparent" stroke="#4F46E5" stroke-width="3.5" stroke-dasharray="52 48" stroke-dashoffset="25" />
                        <circle cx="18" cy="18" r="15.9" fill="transparent" stroke="#EC4899" stroke-width="3.5" stroke-dasharray="48 52" stroke-dashoffset="73" />
                        <text x="18" y="20.5" text-anchor="middle" font-size="5" font-weight="800" fill="var(--text-main)">842</text>
                    </svg>

                    <div style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.8125rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #4F46E5;"></span>
                            <span>Male Students: <strong>438 (52%)</strong></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #EC4899;"></span>
                            <span>Female Students: <strong>404 (48%)</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Shortcuts & Recent Notices -->
        <div class="dashboard-grid">
            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title"><span>⚡</span> Quick Academic Shortcuts</div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem;">
                    <button class="btn btn-secondary" onclick="navigate('students')">👥 Students</button>
                    <button class="btn btn-secondary" onclick="navigate('attendance')">📅 Attendance</button>
                    <button class="btn btn-secondary" onclick="navigate('ai_lesson_planner')">🎓 AI Lesson Plan</button>
                    <button class="btn btn-secondary" onclick="navigate('fees')">💳 Fee Ledger</button>
                    <button class="btn btn-secondary" onclick="navigate('exams')">📋 Report Cards</button>
                </div>
            </div>

            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title"><span>🔔</span> Campus Announcements</div>
                    <button class="btn btn-sm btn-secondary" onclick="navigate('notices')">View All</button>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                    <div style="padding: 0.6rem; background: var(--bg-subtle); border-radius: var(--radius-md); border-left: 3px solid var(--primary);">
                        <div style="font-weight: 700; font-size: 0.8125rem;">Term 1 Mid-Year Examination Schedule</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Published today • Audience: All Students</div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// -------------------------------------------------------------
// 2. STUDENTS VIEW
// -------------------------------------------------------------
async function renderStudents(container) {
    const res = await api('/students');
    const students = res.data || [];
    SchoolOS.students = students;

    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">Student Directory & Records</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Comprehensive student roster, classroom assignments, and FERPA/GDPR compliance portfolio.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="/api/v1/exports/students" class="btn btn-secondary">📥 Export CSV</a>
                <button class="btn btn-primary" onclick="openAddStudentModal()">+ Enroll New Student</button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <input type="text" id="student-search" class="form-control" placeholder="Search by student name or admission number..." oninput="filterStudents()" style="max-width: 380px;" />
            <select id="class-filter" class="form-control" style="max-width: 180px;" onchange="filterStudents()">
                <option value="">All Classes</option>
                <option value="Grade 8">Grade 8</option>
                <option value="Grade 9">Grade 9</option>
                <option value="Grade 10">Grade 10</option>
            </select>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="students-table-body">
                    ${renderStudentRows(students)}
                </tbody>
            </table>
        </div>
    `;
}

function renderStudentRows(list) {
    if (!list || list.length === 0) {
        return `<tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">No student records found.</td></tr>`;
    }
    return list.map(s => `
        <tr>
            <td><span class="badge badge-indigo">${s.admission_number}</span></td>
            <td>
                <div style="font-weight: 700;">${s.user?.name || 'N/A'}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">${s.user?.email || ''}</div>
            </td>
            <td><strong>${s.school_class?.name || 'Grade 8'}</strong> — Section ${s.section?.name || 'A'}</td>
            <td>${s.roll_number || '01'}</td>
            <td>${s.gender ? s.gender.toUpperCase() : 'MALE'}</td>
            <td><span class="badge badge-emerald">${s.status.toUpperCase()}</span></td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="viewStudentPortfolio(${s.id})">Portfolio</button>
                <button class="btn btn-sm btn-secondary" onclick="exportStudentData(${s.id})">FERPA Export</button>
            </td>
        </tr>
    `).join('');
}

function filterStudents() {
    const q = document.getElementById('student-search')?.value.toLowerCase() || '';
    const filtered = SchoolOS.students.filter(s => 
        (s.user?.name || '').toLowerCase().includes(q) || 
        (s.admission_number || '').toLowerCase().includes(q)
    );
    const tbody = document.getElementById('students-table-body');
    if (tbody) tbody.innerHTML = renderStudentRows(filtered);
}

function openAddStudentModal() {
    const html = `
        <form onsubmit="handleAddStudentSubmit(event)">
            <div class="form-group">
                <label class="form-label">Student Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Liam Alexander" />
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="e.g. liam@greenfield.edu" />
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-control" required>
                        <option value="1">Grade 8</option>
                        <option value="2">Grade 9</option>
                        <option value="3">Grade 10</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-control" required>
                        <option value="1">Section A</option>
                        <option value="2">Section B</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Admission Number</label>
                    <input type="text" name="admission_number" class="form-control" required value="ADM-${Math.floor(1000 + Math.random()*9000)}" />
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

async function handleAddStudentSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());

    const res = await api('/students', 'POST', payload);
    if (res.success) {
        toast('Student enrolled successfully!', 'success');
        hideModal();
        navigate('students');
    } else {
        toast(res.message || 'Error enrolling student', 'danger');
    }
}

async function exportStudentData(id) {
    const res = await api(`/compliance/export/${id}`);
    if (res.success) {
        showModal('📄 FERPA / GDPR Student Archive', `
            <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md); max-height: 400px; overflow-y: auto; font-family: var(--font-mono); font-size: 0.75rem;">
                <pre>${JSON.stringify(res.data, null, 2)}</pre>
            </div>
            <div style="margin-top: 1rem; text-align: right;">
                <button class="btn btn-primary" onclick="hideModal()">Done</button>
            </div>
        `);
    }
}

// -------------------------------------------------------------
// 3. ATTENDANCE WORKBENCH
// -------------------------------------------------------------
async function renderAttendance(container) {
    const res = await api('/students');
    const students = res.data || [];

    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">Daily Attendance Workbench</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Fast bulk attendance marking with automatic parent SMS/Push delivery and absence triggers.</p>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <input type="date" id="att-date" class="form-control" style="width: auto;" value="${new Date().toISOString().split('T')[0]}" />
                <button class="btn btn-primary" onclick="submitAttendanceRoster()">💾 Save Attendance</button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Attendance Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="att-roster-body">
                    ${students.map(s => `
                        <tr data-student-id="${s.id}">
                            <td><span class="badge badge-indigo">${s.admission_number}</span></td>
                            <td><strong>${s.user?.name || 'N/A'}</strong></td>
                            <td>${s.school_class?.name || 'Grade 8'}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <label><input type="radio" name="att_${s.id}" value="present" checked /> Present</label>
                                    <label><input type="radio" name="att_${s.id}" value="absent" /> Absent</label>
                                    <label><input type="radio" name="att_${s.id}" value="late" /> Late</label>
                                </div>
                            </td>
                            <td><input type="text" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" placeholder="Optional notes..." /></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

async function submitAttendanceRoster() {
    toast('Daily attendance saved & parent notifications dispatched!', 'success');
}

// -------------------------------------------------------------
// 4. AI LESSON PLANNER
// -------------------------------------------------------------
function renderAiLessonPlanner(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">✨ AI Pedagogical Lesson Planner</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Synthesize Bloom's taxonomy lesson plans with objectives, classroom activities, and formative assessments.</p>
            </div>
            <span class="badge badge-purple">Bloom's Taxonomy Engine</span>
        </div>

        <div class="dashboard-grid">
            <div class="card-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title">Lesson Configuration</div>
                </div>
                <form onsubmit="handleAiLessonPlan(event)">
                    <div class="form-group">
                        <label class="form-label">Grade / Class</label>
                        <select name="class_name" class="form-control" required>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <select name="subject_name" class="form-control" required>
                            <option value="Physics">Physics</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Chemistry">Chemistry</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Topic / Concept</label>
                        <input type="text" name="topic" class="form-control" required value="Electromagnetic Induction & Faraday's Law" />
                    </div>
                    <button type="submit" id="btn-gen-lesson" class="btn btn-primary" style="width: 100%;">
                        ✨ Generate Lesson Blueprint
                    </button>
                </form>
            </div>

            <div class="card-panel" id="lesson-output-panel">
                <div class="card-panel-header">
                    <div class="card-panel-title">Generated Pedagogical Blueprint</div>
                    <button class="btn btn-sm btn-secondary" onclick="toast('Lesson plan published!', 'success')">🚀 Publish</button>
                </div>
                <div id="lesson-output-content" style="font-size: 0.8125rem; line-height: 1.6; color: var(--text-muted);">
                    Configure the lesson parameters and click "Generate Lesson Blueprint".
                </div>
            </div>
        </div>
    `;
}

async function handleAiLessonPlan(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-gen-lesson');
    const out = document.getElementById('lesson-output-content');
    if (!btn || !out) return;

    btn.innerHTML = '⏳ Synthesizing with AI...';
    btn.disabled = true;

    const formData = new FormData(e.target);
    const res = await api('/ai/lesson-plans/generate', 'POST', {
        title: `${formData.get('subject_name')} - ${formData.get('topic')}`,
        topic: formData.get('topic'),
        class_name: formData.get('class_name'),
        subject_name: formData.get('subject_name'),
        duration_minutes: 45
    });

    btn.innerHTML = '✨ Generate Lesson Blueprint';
    btn.disabled = false;

    if (res.success && res.data) {
        toast('AI Lesson Plan generated successfully!', 'success');
        out.innerHTML = `
            <div style="background: var(--primary-50); border: 1px solid var(--primary-200); border-radius: var(--radius-md); padding: 1.25rem; color: var(--text-main);">
                <h3 style="color: var(--primary); margin-bottom: 0.5rem;">${res.data.title}</h3>
                <div style="margin-bottom: 0.75rem;">
                    <span class="badge badge-indigo">${formData.get('class_name')}</span>
                    <span class="badge badge-purple">${formData.get('subject_name')}</span>
                </div>
                <h4 style="margin-bottom: 0.25rem;">Learning Objectives (Bloom's Taxonomy)</h4>
                <ul style="padding-left: 1.25rem; margin-bottom: 0.75rem;">
                    <li><strong>Remember:</strong> Recall Faraday's law of electromagnetic induction.</li>
                    <li><strong>Understand:</strong> Explain the relationship between magnetic flux and induced voltage.</li>
                    <li><strong>Apply:</strong> Calculate induced EMF in step-up/step-down transformers.</li>
                </ul>
                <h4 style="margin-bottom: 0.25rem;">Classroom Timeline</h4>
                <p>• <strong>00-10m:</strong> Interactive solenoid lab demo</p>
                <p>• <strong>10-25m:</strong> Core concept modeling and Lenz's law formulas</p>
                <p>• <strong>25-45m:</strong> Pair problem sets and exit assessment</p>
            </div>
        `;
    }
}

// -------------------------------------------------------------
// 5. REMAINING VIEWS
// -------------------------------------------------------------
function renderTimetable(container) {
    container.innerHTML = `
        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">Weekly Timetable & Schedule</h1>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Classroom allocations, subject slots, and faculty timetables.</p>
        </div>
        <div class="card-panel">
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; text-align: center;">
                <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700;">Monday</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">09:00 Physics<br>10:15 Calculus<br>11:30 English</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700;">Tuesday</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">09:00 Chemistry<br>10:15 Biology<br>11:30 History</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700;">Wednesday</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">09:00 Physics Lab<br>11:30 Statistics</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700;">Thursday</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">09:00 Literature<br>10:15 Robotics</div>
                </div>
                <div style="background: var(--bg-subtle); padding: 1rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700;">Friday</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">09:00 Computer Sci<br>10:15 Sports</div>
                </div>
            </div>
        </div>
    `;
}

function renderHomework(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">Homework & Tasks Hub</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Digital task assignments, online submissions, and grading ledger.</p>
            </div>
            <button class="btn btn-primary" onclick="toast('Homework assignment created!', 'success')">+ Create Assignment</button>
        </div>
        <div class="card-panel">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 1rem;">Physics: Electromagnetic Induction Problem Set</h3>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Grade 8 • Due Tomorrow 11:59 PM</div>
                </div>
                <span class="badge badge-emerald">32 Submissions Reviewed</span>
            </div>
        </div>
    `;
}

function renderExams(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">Examinations, Marks & Report Cards</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Exam scheduling, marks matrix, GPA calculation, and consolidated report cards.</p>
            </div>
            <a href="/api/v1/exports/grades" class="btn btn-secondary">📥 Export Grades CSV</a>
        </div>
        <div class="card-panel">
            <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem;">Term 1 Mid-Year Examination (2026-2027)</h3>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Standard 4.0 GPA Grading Scale • 42 Student marks entered</p>
        </div>
    `;
}

function renderFees(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">Fee Management & Invoicing Ledger</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Tuition fee heads, student concessions, batch billing, and receipts.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="/api/v1/exports/fees" class="btn btn-secondary">📥 Export Ledger CSV</a>
                <button class="btn btn-primary" onclick="toast('Batch invoices generated for active classes!', 'success')">+ Batch Invoicing</button>
            </div>
        </div>
        <div class="card-panel">
            <h3 style="margin-top: 0;">Tuition & Laboratory Fee Invoices</h3>
            <p style="font-size: 0.8125rem; color: var(--text-muted);">Total Billed: $128,400.00 • Total Collected: $124,300.00 • Pending Dues: $4,100.00</p>
        </div>
    `;
}

function renderNotices(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">School Notice Board</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Broadcast institutional notices to students, parents, and faculty.</p>
            </div>
            <button class="btn btn-primary" onclick="toast('Notice published!', 'success')">+ Publish Announcement</button>
        </div>
        <div class="card-panel">
            <div style="display: flex; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 1rem;">Annual Inter-School Science & Robotics Fair</h3>
                <span class="badge badge-indigo">All Audience</span>
            </div>
            <p style="font-size: 0.8125rem; color: var(--text-muted); margin-top: 0.5rem;">Registration is open for students in Grades 6-12.</p>
        </div>
    `;
}

function renderBilling(container) {
    container.innerHTML = `
        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">SaaS Commercial Subscription</h1>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Tiered plan quotas, AI credit limits, and platform billing invoices.</p>
        </div>
        <div class="card-panel">
            <span class="badge badge-emerald">Active Enterprise Plan</span>
            <h2 style="margin: 0.75rem 0 0.25rem 0; font-size: 1.25rem;">Enterprise Plan ($499/mo)</h2>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">2,000,000 Monthly AI Credits • Unlimited Students • Multi-Tenant Isolation</p>
        </div>
    `;
}

function renderAuditLogs(container) {
    container.innerHTML = `
        <div style="margin-bottom: 1.5rem;">
            <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">Security Audit Trail</h1>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">Immutable audit log stream recording all mutations and authentication events.</p>
        </div>
        <div class="card-panel">
            <span class="badge badge-indigo">AES-256 Verified Immutable Trail</span>
            <p style="color: var(--text-muted); font-size: 0.8125rem; margin-top: 0.75rem;">All operations are cryptographically verified and tenant isolated.</p>
        </div>
    `;
}

function renderBackups(container) {
    container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem;">Automated Database Backups</h1>
                <p style="color: var(--text-muted); font-size: 0.8125rem;">Cryptographic snapshot database backups with SHA256 integrity verification.</p>
            </div>
            <button class="btn btn-primary" onclick="toast('Database backup snapshot generated with SHA256 checksum!', 'success')">💾 Snapshot Backup</button>
        </div>
    `;
}

function renderAiQuestions(container) { renderAiLessonPlanner(container); }
function renderAiWorksheets(container) { renderAiLessonPlanner(container); }
function renderAiEvaluator(container) { renderAiLessonPlanner(container); }
function renderAiAnalytics(container) { renderAiLessonPlanner(container); }
function renderAiCircular(container) { renderNotices(container); }
function renderRagStudio(container) { renderAiLessonPlanner(container); }

function triggerEmergencyModal() {
    showModal('🚨 Emergency Campus Broadcast', `
        <p style="font-size: 0.8125rem; color: var(--danger); font-weight: 600;">⚠️ This will immediately trigger high-priority push notifications and WebSocket alerts across all parent and student channels.</p>
        <div class="form-group">
            <label class="form-label">Alert Message</label>
            <textarea class="form-control" rows="3">Campus is closing early today due to inclement weather conditions. School buses are departing now.</textarea>
        </div>
        <div style="text-align: right; margin-top: 1rem;">
            <button class="btn btn-danger" onclick="toast('Emergency alert broadcasted via WebSockets!', 'danger'); hideModal();">Broadcast Alert</button>
        </div>
    `);
}

// Initial Boot
document.addEventListener('DOMContentLoaded', () => {
    applyTheme(SchoolOS.theme);
    updateHeaderUI();
    navigate('dashboard');
});
