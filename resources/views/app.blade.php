<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI SchoolOS — Modern SaaS Multi-Tenant School Management Platform</title>
    <link rel="stylesheet" href="/css/schoolos-design-system.css">
</head>
<body>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <div class="brand-badge">OS</div>
                <div>
                    <div style="font-weight: 800; font-size: 1.05rem; letter-spacing: -0.02em; line-height: 1.1;">AI SchoolOS</div>
                    <div style="font-size: 0.6875rem; color: var(--text-light); font-weight: 700; text-transform: uppercase;">Enterprise SaaS</div>
                </div>
            </div>

            <!-- Nav Groups -->
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item active" data-tab="dashboard" onclick="navigate('dashboard')">
                    <span class="icon">📊</span>
                    <span>Dashboard</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Academic Operations</div>
                <div class="nav-item" data-tab="students" onclick="navigate('students')">
                    <span class="icon">👥</span>
                    <span>Student Directory</span>
                </div>
                <div class="nav-item" data-tab="attendance" onclick="navigate('attendance')">
                    <span class="icon">📅</span>
                    <span>Daily Attendance</span>
                </div>
                <div class="nav-item" data-tab="timetable" onclick="navigate('timetable')">
                    <span class="icon">⏰</span>
                    <span>Weekly Timetable</span>
                </div>
                <div class="nav-item" data-tab="homework" onclick="navigate('homework')">
                    <span class="icon">📖</span>
                    <span>Homework & Tasks</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Examination & Grading</div>
                <div class="nav-item" data-tab="exams" onclick="navigate('exams')">
                    <span class="icon">📋</span>
                    <span>Exams & Report Cards</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Finance & Billing</div>
                <div class="nav-item" data-tab="fees" onclick="navigate('fees')">
                    <span class="icon">💳</span>
                    <span>Fee Management</span>
                </div>
                <div class="nav-item" data-tab="billing" onclick="navigate('billing')">
                    <span class="icon">⭐</span>
                    <span>SaaS Subscription</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">✨ AI Pedagogical Suite</div>
                <div class="nav-item" data-tab="ai_lesson_planner" onclick="navigate('ai_lesson_planner')">
                    <span class="icon">🎓</span>
                    <span>AI Lesson Planner</span>
                </div>
                <div class="nav-item" data-tab="ai_questions" onclick="navigate('ai_questions')">
                    <span class="icon">📝</span>
                    <span>Question Papers</span>
                </div>
                <div class="nav-item" data-tab="ai_worksheets" onclick="navigate('ai_worksheets')">
                    <span class="icon">📄</span>
                    <span>AI Worksheets</span>
                </div>
                <div class="nav-item" data-tab="ai_evaluator" onclick="navigate('ai_evaluator')">
                    <span class="icon">🔍</span>
                    <span>OCR Answer Evaluator</span>
                </div>
                <div class="nav-item" data-tab="ai_analytics" onclick="navigate('ai_analytics')">
                    <span class="icon">📈</span>
                    <span>Student Analytics</span>
                </div>
                <div class="nav-item" data-tab="rag_studio" onclick="navigate('rag_studio')">
                    <span class="icon">🧠</span>
                    <span>Curriculum RAG</span>
                </div>
            </div>

            <div class="nav-section" style="margin-top: auto; border-top: 1px solid var(--border-subtle); padding-top: 0.5rem;">
                <div class="nav-section-title">Administration</div>
                <div class="nav-item" data-tab="notices" onclick="navigate('notices')">
                    <span class="icon">🔔</span>
                    <span>Notice Board</span>
                </div>
                <div class="nav-item" data-tab="audit_logs" onclick="navigate('audit_logs')">
                    <span class="icon">🛡️</span>
                    <span>Security Audit Trail</span>
                </div>
                <div class="nav-item" data-tab="backups" onclick="navigate('backups')">
                    <span class="icon">💾</span>
                    <span>Database Backups</span>
                </div>
                <a href="/docs" class="nav-item" style="color: var(--text-muted);">
                    <span class="icon">📘</span>
                    <span>API Reference (/docs)</span>
                </a>
            </div>
        </aside>

        <!-- App Main Content Area -->
        <div class="app-main">
            <!-- Header -->
            <header class="app-header">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="tenant-selector-pill">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--success);"></span>
                        <span id="tenant-name-label">Greenfield International Academy</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <!-- Theme Toggle -->
                    <button id="theme-toggle-btn" class="btn btn-secondary btn-sm" onclick="toggleTheme()" title="Toggle Light / Dark Theme" style="padding: 0.4rem 0.6rem; font-size: 1rem;">
                        🌙
                    </button>

                    <!-- Fast Role Switcher -->
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Role View:</span>
                        <select class="role-switcher-dropdown" onchange="switchRole(this.value)">
                            <option value="school_admin">🏫 School Admin / Principal</option>
                            <option value="teacher">👩‍🏫 Faculty / Teacher</option>
                            <option value="student">🎓 Student / Parent</option>
                            <option value="super_admin">👑 Platform Super Admin</option>
                        </select>
                    </div>

                    <div style="height: 24px; width: 1px; background: var(--border);"></div>

                    <!-- User Profile -->
                    <div class="user-profile-widget">
                        <div class="user-avatar">SJ</div>
                        <div>
                            <div id="user-name-label" style="font-weight: 700; font-size: 0.8125rem; line-height: 1.2;">Dr. Sarah Jenkins</div>
                            <div id="user-role-label" style="font-size: 0.6875rem; color: var(--text-muted);">Principal / Admin</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dynamic Viewport -->
            <main class="app-viewport" id="viewport">
                <!-- Rendered dynamically by schoolos-app.js -->
            </main>
        </div>
    </div>

    <!-- Modals & Toasts -->
    <div class="modal-overlay" id="modal-overlay" onclick="hideModal()">
        <div class="modal-dialog" id="modal-dialog-content" onclick="event.stopPropagation()"></div>
    </div>

    <div class="toast-shelf" id="toast-shelf"></div>

    <!-- Client Script -->
    <script src="/js/schoolos-app.js"></script>
</body>
</html>
