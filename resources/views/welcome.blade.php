<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI SchoolOS — Multi-Tenant SaaS Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --primary-light: #EEF2FF;
            --secondary: #0EA5E9;
            --success: #10B981;
            --success-light: #ECFDF5;
            --warning: #F59E0B;
            --danger: #EF4444;
            --bg-app: #F8FAFC;
            --surface: #FFFFFF;
            --border: #E2E8F0;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-app);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--text-main);
            text-decoration: none;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }

        .badge-tenancy {
            background: var(--primary-light);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Container Layout */
        .container {
            max-width: 1200px;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
            width: 100%;
            flex: 1;
        }

        /* Auth Card */
        .auth-wrapper {
            max-width: 460px;
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
        }

        .auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .auth-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.85rem 1.25rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-upgrade {
            background: linear-gradient(135deg, #4F46E5, #0EA5E9);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-upgrade:hover { opacity: 0.9; }

        /* Demo Quick Pickers */
        .quick-roles {
            margin-top: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px dashed var(--border);
        }

        .quick-roles-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 0.75rem;
        }

        .role-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .role-chip {
            background: #F1F5F9;
            border: 1px solid var(--border);
            padding: 0.4rem 0.7rem;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.15s;
        }

        .role-chip:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary);
        }

        /* Dashboard View (Post-Auth) */
        .dashboard-view {
            display: none;
        }

        .dash-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            background: var(--surface);
            padding: 1.5rem 2rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .dash-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            padding: 1.5rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .stat-card-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .stat-card-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .progress-bar-bg {
            background: #E2E8F0;
            height: 8px;
            border-radius: 4px;
            margin-top: 0.75rem;
            overflow: hidden;
        }
        .progress-bar-fill {
            background: var(--primary);
            height: 100%;
            border-radius: 4px;
        }

        .tenant-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .perm-badge {
            display: inline-block;
            background: #F1F5F9;
            color: #334155;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin: 0.2rem;
        }

        .alert-box {
            padding: 0.85rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
            display: none;
        }
        .alert-error {
            background: #FEF2F2;
            color: var(--danger);
            border: 1px solid #FEE2E2;
        }
        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid #D1FAE5;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-outline:hover {
            background: #F1F5F9;
        }

        /* Plans Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
            padding: 1.5rem;
        }
        .modal-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            max-width: 800px;
            width: 100%;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            max-height: 90vh;
            overflow-y: auto;
        }
        .plan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        .plan-item {
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .plan-item:hover, .plan-item.selected {
            border-color: var(--primary);
            background: var(--primary-light);
        }
    </style>
</head>
<body>

<header>
    <a href="/" class="brand">
        <div class="brand-logo">✨</div>
        <span>AI SchoolOS</span>
    </a>
    <div id="header-user-info" style="display: flex; align-items: center; gap: 1rem;">
        <span class="badge-tenancy" id="tenant-badge">⚡ Multi-Tenant Mode</span>
        <button id="btn-logout" class="btn-outline" style="display: none;" onclick="logout()">Sign Out</button>
    </div>
</header>

<div class="container">
    <!-- 1. Unified Authentication Card -->
    <div class="auth-wrapper" id="auth-section">
        <h1 class="auth-title">Welcome to AI SchoolOS</h1>
        <p class="auth-subtitle">Unified School Management & AI Education SaaS</p>

        <div id="auth-alert" class="alert-box alert-error"></div>

        <form id="login-form" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label for="email">Institutional Email</label>
                <input type="email" id="email" required placeholder="name@school.edu" value="admin@greenfield.edu">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" required value="password123">
            </div>

            <button type="submit" id="btn-submit" class="btn-primary" style="width: 100%;">
                <span>Sign In to SchoolOS</span>
                <span>→</span>
            </button>
        </form>

        <div class="quick-roles">
            <div class="quick-roles-title">⚡ Quick Switch Demo Personas</div>
            <div class="role-chips">
                <div class="role-chip" id="chip-school-admin" onclick="fillCreds('admin@greenfield.edu', 'Greenfield Admin')">🏫 School Admin (Greenfield)</div>
                <div class="role-chip" id="chip-teacher" onclick="fillCreds('teacher@greenfield.edu', 'Science Teacher')">👩‍🏫 Teacher (Science)</div>
                <div class="role-chip" id="chip-student" onclick="fillCreds('student@greenfield.edu', 'Alex Miller')">🎓 Student (Alex)</div>
                <div class="role-chip" id="chip-parent" onclick="fillCreds('parent@greenfield.edu', 'Robert Miller')">👨‍👩‍👧 Parent (Robert)</div>
                <div class="role-chip" id="chip-super-admin" onclick="fillCreds('superadmin@schoolos.com', 'Super Admin')">👑 Super Admin</div>
                <div class="role-chip" id="chip-school-b" onclick="fillCreds('admin@oakridge.edu', 'Oakridge Admin')">🏢 School B (Oakridge)</div>
            </div>
        </div>
    </div>

    <!-- 2. Role-Tailored Dashboard View -->
    <div class="dashboard-view" id="dashboard-section">
        <div class="dash-header">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800;" id="user-greeting">Good morning 👋</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.25rem;" id="user-role-desc">Role: School Administrator</p>
            </div>
            <div style="text-align: right;">
                <div style="font-weight: 700; font-size: 1.1rem; color: var(--primary);" id="tenant-name">Greenfield International School</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);" id="tenant-subdomain">Subdomain: greenfield.schoolos.com</div>
            </div>
        </div>

        <!-- School Admin Subscription & Quotas Card -->
        <div class="tenant-panel" id="subscription-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <div>
                    <h2 style="font-size: 1.2rem; font-weight: 700;">💳 Current SaaS Subscription: <span id="current-plan-name" style="color: var(--primary);">Professional</span></h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted);" id="plan-billing-status">Status: Active (Renews in 15 days)</p>
                </div>
                <button id="btn-open-upgrade" class="btn-upgrade" onclick="openUpgradeModal()">⚡ Upgrade Plan</button>
            </div>

            <div class="dash-grid" style="margin-bottom: 0;">
                <div style="background: #F8FAFC; padding: 1rem; border-radius: 8px; border: 1px solid var(--border);">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Enrolled Students</div>
                    <div style="font-size: 1.3rem; font-weight: 800;" id="quota-students">4 / 2,000</div>
                    <div class="progress-bar-bg"><div class="progress-bar-fill" id="bar-students" style="width: 2%;"></div></div>
                </div>

                <div style="background: #F8FAFC; padding: 1rem; border-radius: 8px; border: 1px solid var(--border);">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">AI Generation Credits</div>
                    <div style="font-size: 1.3rem; font-weight: 800; color: var(--primary);" id="quota-ai">10,000 / 10,000</div>
                    <div class="progress-bar-bg"><div class="progress-bar-fill" id="bar-ai" style="width: 100%; background: var(--primary);"></div></div>
                </div>

                <div style="background: #F8FAFC; padding: 1rem; border-radius: 8px; border: 1px solid var(--border);">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Cloud Storage</div>
                    <div style="font-size: 1.3rem; font-weight: 800;" id="quota-storage">1.2 GB / 50 GB</div>
                    <div class="progress-bar-bg"><div class="progress-bar-fill" id="bar-storage" style="width: 2.4%; background: var(--secondary);"></div></div>
                </div>
            </div>
        </div>

        <!-- Super Admin Commercial Control Plane (Conditional) -->
        <div class="tenant-panel" id="platform-metrics-panel" style="display: none;">
            <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem;">👑 Global SaaS Platform Revenue & AI Metrics</h2>
            <div class="dash-grid" style="margin-bottom: 0;">
                <div class="stat-card">
                    <div class="stat-card-title">Monthly Recurring Revenue (MRR)</div>
                    <div class="stat-card-value" style="color: var(--success);" id="metric-mrr">$331.50</div>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">ARR: $3,978.00</p>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Active Schools Onboarded</div>
                    <div class="stat-card-value" id="metric-schools">2 Schools</div>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">0 Trials Expiring</p>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Total AI Cost Meter</div>
                    <div class="stat-card-value" style="color: var(--primary);" id="metric-ai-cost">$0.0000</div>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Tokens: 0 M</p>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            <div class="stat-card">
                <div class="stat-card-title">Tenant Isolation Status</div>
                <div class="stat-card-value" style="color: var(--success); font-size: 1.4rem;">✓ Strictly Isolated</div>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;" id="tenant-id-label">Active Tenant ID: #1</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-title">Assigned Role Scope</div>
                <div class="stat-card-value" id="user-role-badge" style="font-size: 1.4rem;">School Admin</div>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Sanctum Token Active</p>
            </div>

            <div class="stat-card">
                <div class="stat-card-title">AI Module Availability</div>
                <div class="stat-card-value" style="color: var(--primary); font-size: 1.4rem;">✨ 6 Modules</div>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Lesson Plan, Paper, Worksheet, OCR</p>
            </div>
        </div>

        <div class="tenant-panel">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">🔑 Active RBAC Granted Permissions</h2>
            <div id="permissions-container" style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                <!-- Filled dynamically -->
            </div>
        </div>

        <div class="tenant-panel" id="invoices-panel">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">📄 Recent Subscription Invoices</h2>
            <div id="invoices-container" style="font-size: 0.85rem;">
                Loading invoices...
            </div>
        </div>

        <div class="tenant-panel">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">🛡️ Live Tenant Isolation Audit Trail</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                Records in this table are filtered strictly to the active school (<code id="code-school-name">Greenfield</code>). School B's events are zero-trust isolated.
            </p>
            <div id="audit-log-container" style="font-size: 0.85rem; background: #F8FAFC; border: 1px solid var(--border); border-radius: 8px; padding: 1rem; max-height: 200px; overflow-y: auto;">
                Loading audit logs...
            </div>
        </div>
    </div>
</div>

<!-- Plan Upgrade Modal -->
<div class="modal-overlay" id="upgrade-modal">
    <div class="modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.3rem; font-weight: 700;">Select SaaS Subscription Plan</h2>
            <button class="btn-outline" onclick="closeUpgradeModal()">✕</button>
        </div>

        <div class="plan-grid">
            <div class="plan-item" id="card-starter" onclick="selectPlan('starter')">
                <h3 style="font-weight: 700;">Starter</h3>
                <div style="font-size: 1.5rem; font-weight: 800; margin: 0.5rem 0;">$99<span style="font-size: 0.8rem; color: var(--text-muted);">/mo</span></div>
                <p style="font-size: 0.75rem; color: var(--text-muted);">500 Students • 1,000 AI Credits • 10GB</p>
            </div>
            <div class="plan-item selected" id="card-professional" onclick="selectPlan('professional')">
                <div style="font-size: 0.65rem; background: var(--primary); color: white; border-radius: 4px; padding: 2px 6px; display: inline-block; margin-bottom: 4px;">POPULAR</div>
                <h3 style="font-weight: 700;">Professional</h3>
                <div style="font-size: 1.5rem; font-weight: 800; margin: 0.5rem 0;">$249<span style="font-size: 0.8rem; color: var(--text-muted);">/mo</span></div>
                <p style="font-size: 0.75rem; color: var(--text-muted);">2,000 Students • 10,000 AI Credits • 50GB</p>
            </div>
            <div class="plan-item" id="card-enterprise" onclick="selectPlan('enterprise')">
                <h3 style="font-weight: 700;">Enterprise</h3>
                <div style="font-size: 1.5rem; font-weight: 800; margin: 0.5rem 0;">$599<span style="font-size: 0.8rem; color: var(--text-muted);">/mo</span></div>
                <p style="font-size: 0.75rem; color: var(--text-muted);">10,000 Students • 50,000 AI Credits • 250GB</p>
            </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label>Coupon Code (Optional)</label>
            <div style="display: flex; gap: 0.5rem;">
                <input type="text" id="coupon-input" placeholder="e.g. WELCOME20 or LAUNCH50">
                <button type="button" class="btn-outline" onclick="applyCoupon()">Apply</button>
            </div>
            <div id="coupon-feedback" style="font-size: 0.8rem; margin-top: 0.35rem;"></div>
        </div>

        <button id="btn-confirm-upgrade" class="btn-primary" style="width: 100%;" onclick="confirmSubscription()">
            Confirm Subscription
        </button>
    </div>
</div>

<script>
    let authToken = localStorage.getItem('schoolos_token');
    let selectedPlanSlug = 'professional';

    function fillCreds(email, name) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password123';
    }

    async function handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const alertBox = document.getElementById('auth-alert');
        alertBox.style.display = 'none';

        try {
            const res = await fetch('/api/v1/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                alertBox.textContent = data.message || 'Login failed.';
                alertBox.style.display = 'block';
                return;
            }

            authToken = data.data.token;
            localStorage.setItem('schoolos_token', authToken);
            renderDashboard(data.data);

        } catch (err) {
            alertBox.textContent = 'Server connection error. Please verify the backend is running.';
            alertBox.style.display = 'block';
        }
    }

    async function checkSession() {
        if (!authToken) return;

        try {
            const res = await fetch('/api/v1/auth/me', {
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                const data = await res.json();
                renderDashboard(data.data);
            } else {
                localStorage.removeItem('schoolos_token');
            }
        } catch (err) {
            console.error('Session check failed', err);
        }
    }

    function renderDashboard(payload) {
        const user = payload.user;
        const tenant = payload.tenant;

        document.getElementById('auth-section').style.display = 'none';
        document.getElementById('dashboard-section').style.display = 'block';
        document.getElementById('btn-logout').style.display = 'block';

        document.getElementById('user-greeting').textContent = `Good morning, ${user.name} 👋`;
        document.getElementById('user-role-desc').textContent = `Role: ${user.role_name} (${user.email})`;
        document.getElementById('user-role-badge').textContent = user.role_name;

        if (tenant) {
            document.getElementById('tenant-name').textContent = tenant.name;
            document.getElementById('tenant-subdomain').textContent = `Tenant: ${tenant.subdomain}.schoolos.com`;
            document.getElementById('tenant-badge').textContent = `🏫 ${tenant.name}`;
            document.getElementById('tenant-id-label').textContent = `Active Tenant ID: #${tenant.id} (${tenant.code})`;
            document.getElementById('code-school-name').textContent = tenant.name;
            document.getElementById('platform-metrics-panel').style.display = 'none';
            document.getElementById('subscription-panel').style.display = 'block';
            document.getElementById('invoices-panel').style.display = 'block';
            fetchBillingDetails();
            fetchInvoices();
            fetchAuditLogs();
        } else {
            // Super Admin Mode
            document.getElementById('tenant-name').textContent = 'Platform Super Admin';
            document.getElementById('tenant-subdomain').textContent = 'Global SaaS Control Plane';
            document.getElementById('tenant-badge').textContent = '👑 Super Admin';
            document.getElementById('tenant-id-label').textContent = 'Platform Scope (All Tenants)';
            document.getElementById('subscription-panel').style.display = 'none';
            document.getElementById('invoices-panel').style.display = 'none';
            document.getElementById('platform-metrics-panel').style.display = 'block';
            document.getElementById('audit-log-container').innerHTML = '<em>Global Super Admin Session active.</em>';
            fetchPlatformMetrics();
        }

        const permContainer = document.getElementById('permissions-container');
        permContainer.innerHTML = '';
        if (user.permissions && user.permissions.length > 0) {
            user.permissions.forEach(p => {
                const badge = document.createElement('span');
                badge.className = 'perm-badge';
                badge.textContent = `✓ ${p}`;
                permContainer.appendChild(badge);
            });
        } else if (user.role === 'super-admin') {
            const badge = document.createElement('span');
            badge.className = 'perm-badge';
            badge.style.background = '#EEF2FF';
            badge.style.color = '#4F46E5';
            badge.textContent = '👑 Full Platform Super Admin Privileges';
            permContainer.appendChild(badge);
        }
    }

    async function fetchBillingDetails() {
        try {
            const res = await fetch('/api/v1/billing/subscription', {
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                const sub = data.data.subscription;
                const usage = data.data.usage;

                if (sub) {
                    document.getElementById('current-plan-name').textContent = sub.plan.name;
                    document.getElementById('plan-billing-status').textContent = `Status: ${sub.status.toUpperCase()} (Billed ${sub.billing_cycle})`;
                }

                document.getElementById('quota-students').textContent = `${usage.students.current} / ${usage.students.limit}`;
                document.getElementById('bar-students').style.width = `${Math.min(100, usage.students.percentage)}%`;

                document.getElementById('quota-ai').textContent = `${usage.ai_credits.remaining.toLocaleString()} / ${usage.ai_credits.limit.toLocaleString()}`;
                document.getElementById('bar-ai').style.width = `${Math.max(5, 100 - usage.ai_credits.percentage)}%`;

                document.getElementById('quota-storage').textContent = `${usage.storage.used_gb} GB / ${usage.storage.limit_gb} GB`;
                document.getElementById('bar-storage').style.width = `${Math.min(100, usage.storage.percentage)}%`;
            }
        } catch (e) {
            console.error('Failed to fetch billing', e);
        }
    }

    async function fetchPlatformMetrics() {
        try {
            const res = await fetch('/api/v1/platform/billing/metrics', {
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                const metrics = data.data;
                document.getElementById('metric-mrr').textContent = `$${metrics.mrr_usd.toFixed(2)}`;
                document.getElementById('metric-schools').textContent = `${metrics.active_schools} Schools`;
                document.getElementById('metric-ai-cost').textContent = `$${metrics.total_ai_cost_usd.toFixed(4)}`;
            }
        } catch (e) {
            console.error('Failed to fetch platform metrics', e);
        }
    }

    async function fetchInvoices() {
        try {
            const res = await fetch('/api/v1/billing/invoices', {
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                const container = document.getElementById('invoices-container');
                if (data.data.length === 0) {
                    container.innerHTML = '<em>No invoices generated yet.</em>';
                } else {
                    container.innerHTML = data.data.map(inv =>
                        `<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                            <span><strong>${inv.invoice_number}</strong> • Total: $${inv.total_amount.toFixed(2)}</span>
                            <span style="color: var(--success); font-weight: 700;">✓ ${inv.status.toUpperCase()}</span>
                        </div>`
                    ).join('');
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function fetchAuditLogs() {
        try {
            const res = await fetch('/api/v1/tenant/audit-logs', {
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                const container = document.getElementById('audit-log-container');
                if (data.data.length === 0) {
                    container.innerHTML = '<em>No audit records logged yet for this school.</em>';
                } else {
                    container.innerHTML = data.data.map(log =>
                        `<div style="padding: 0.35rem 0; border-bottom: 1px solid #E2E8F0;">
                            <strong>${log.event}</strong> by <code>${log.user?.email || 'System'}</code> at ${new Date(log.created_at).toLocaleTimeString()}
                        </div>`
                    ).join('');
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    function openUpgradeModal() {
        document.getElementById('upgrade-modal').style.display = 'flex';
    }
    function closeUpgradeModal() {
        document.getElementById('upgrade-modal').style.display = 'none';
    }

    function selectPlan(slug) {
        selectedPlanSlug = slug;
        document.querySelectorAll('.plan-item').forEach(el => el.classList.remove('selected'));
        document.getElementById(`card-${slug}`).classList.add('selected');
    }

    async function applyCoupon() {
        const code = document.getElementById('coupon-input').value;
        const feedback = document.getElementById('coupon-feedback');
        if (!code) return;

        try {
            const res = await fetch('/api/v1/billing/coupons/validate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' },
                body: JSON.stringify({ code, plan_slug: selectedPlanSlug, billing_cycle: 'monthly' })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                feedback.style.color = 'var(--success)';
                feedback.textContent = `✓ ${data.data.description} ($${data.data.discount_amount} off)`;
            } else {
                feedback.style.color = 'var(--danger)';
                feedback.textContent = data.message || 'Invalid coupon';
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function confirmSubscription() {
        const couponCode = document.getElementById('coupon-input').value;
        try {
            const res = await fetch('/api/v1/billing/subscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' },
                body: JSON.stringify({ plan_slug: selectedPlanSlug, billing_cycle: 'monthly', coupon_code: couponCode || null })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                closeUpgradeModal();
                fetchBillingDetails();
                fetchInvoices();
                alert(data.message);
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function logout() {
        try {
            await fetch('/api/v1/auth/logout', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
        } finally {
            localStorage.removeItem('schoolos_token');
            authToken = null;
            document.getElementById('dashboard-section').style.display = 'none';
            document.getElementById('auth-section').style.display = 'block';
            document.getElementById('btn-logout').style.display = 'none';
            document.getElementById('tenant-badge').textContent = '⚡ Multi-Tenant Mode';
        }
    }

    checkSession();
</script>
</body>
</html>
