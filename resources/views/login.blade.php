<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — eschoolAI Enterprise Platform</title>
    <link rel="stylesheet" href="/css/schoolos-design-system.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 10% 20%, var(--bg-subtle), var(--bg-page));
            padding: 1.5rem;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: 2.25rem 2rem;
            position: relative;
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .login-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .theme-switcher-corner {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
        }
    </style>
</head>
<body>

    <div class="theme-switcher-corner">
        <button id="theme-btn" class="btn btn-secondary btn-sm" onclick="toggleTheme()" style="padding: 0.35rem 0.6rem;">
            🌙
        </button>
    </div>

    <div class="login-card">
        <div class="login-header">
            <div class="login-brand">
                <div class="brand-badge" style="width: 42px; height: 42px; font-size: 1.15rem;">eS</div>
                <div style="text-align: left;">
                    <div style="font-weight: 800; font-size: 1.3rem; letter-spacing: -0.02em; line-height: 1.1; color: var(--text-main);">eschoolAI</div>
                    <div style="font-size: 0.75rem; color: var(--brand-orange); font-weight: 700; text-transform: uppercase;">Enterprise Education SaaS</div>
                </div>
            </div>
            <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">Welcome back</h2>
            <p style="font-size: 0.8125rem; color: var(--text-muted); margin: 0;">Sign in to access your institutional portal & AI tools</p>
        </div>

        <form id="login-form" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" id="email" class="form-control" required placeholder="admin@greenfield.edu" value="admin@greenfield.edu" />
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" id="password" class="form-control" required placeholder="••••••••" value="password123" />
            </div>

            <div class="form-group">
                <label class="form-label">School Subdomain / Code (Optional)</label>
                <input type="text" id="school_code" class="form-control" placeholder="greenfield" value="greenfield" />
            </div>

            <button type="submit" id="btn-submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.925rem; margin-top: 0.5rem;">
                🔑 Sign In to Portal
            </button>
        </form>

        <div style="margin-top: 1.75rem; border-top: 1px solid var(--border-subtle); padding-top: 1.25rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; text-align: center;">
                ⚡ 1-Click Quick Demo Accounts
            </div>

            <div class="demo-auth-grid">
                <div class="demo-auth-card" onclick="quickFill('admin@greenfield.edu', 'password123')">
                    <div style="font-weight: 700; font-size: 0.8125rem;">🏫 School Admin</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">admin@greenfield.edu</div>
                </div>

                <div class="demo-auth-card" onclick="quickFill('teacher@greenfield.edu', 'password123')">
                    <div style="font-weight: 700; font-size: 0.8125rem;">👩‍🏫 Teacher</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">teacher@greenfield.edu</div>
                </div>

                <div class="demo-auth-card" onclick="quickFill('student@greenfield.edu', 'password123')">
                    <div style="font-weight: 700; font-size: 0.8125rem;">🎓 Student</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">student@greenfield.edu</div>
                </div>

                <div class="demo-auth-card" onclick="quickFill('superadmin@schoolos.com', 'password123')">
                    <div style="font-weight: 700; font-size: 0.8125rem;">👑 Super Admin</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">superadmin@schoolos.com</div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-shelf" id="toast-shelf"></div>

    <script>
        let theme = localStorage.getItem('schoolos_theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
        updateThemeBtn();

        function toggleTheme() {
            theme = theme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('schoolos_theme', theme);
            updateThemeBtn();
        }

        function updateThemeBtn() {
            const btn = document.getElementById('theme-btn');
            if (btn) btn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
        }

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
                setTimeout(() => el.remove(), 250);
            }, 3000);
        }

        async function quickFill(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            await handleLogin(null);
        }

        async function handleLogin(e) {
            if (e) e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const schoolCode = document.getElementById('school_code').value;
            const btn = document.getElementById('btn-submit');

            btn.innerHTML = '⏳ Authenticating...';
            btn.disabled = true;

            try {
                const res = await fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email, password, school_code: schoolCode || null })
                });

                const data = await res.json();
                btn.innerHTML = '🔑 Sign In to Portal';
                btn.disabled = false;

                if (data.success && data.data) {
                    localStorage.setItem('schoolos_token', data.data.token);
                    localStorage.setItem('schoolos_user', JSON.stringify(data.data.user));
                    if (data.data.tenant) {
                        localStorage.setItem('schoolos_tenant', JSON.stringify(data.data.tenant));
                    }
                    toast('Authentication successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/portal';
                    }, 600);
                } else {
                    toast(data.message || 'Invalid email or password.', 'danger');
                }
            } catch (err) {
                btn.innerHTML = '🔑 Sign In to Portal';
                btn.disabled = false;
                toast(err.message, 'danger');
            }
        }
    </script>
</body>
</html>
