<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eschoolAI — Multi-Tenant SaaS School Management & AI Education Platform</title>
    <link rel="stylesheet" href="/css/schoolos-design-system.css">
</head>
<body>

    <div class="app-container">
        <!-- Sidebar Navigation (All 16 Modules Matching Reference) -->
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <div class="brand-badge">eS</div>
                <div>
                    <div style="font-weight: 800; font-size: 1.05rem; letter-spacing: -0.02em; line-height: 1.1; color: var(--text-main);">eschoolAI</div>
                    <div style="font-size: 0.6875rem; color: var(--text-light); font-weight: 700; text-transform: uppercase;">Enterprise SaaS</div>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Navigation</div>
                <div class="nav-item" data-tab="dashboard" onclick="navigate('dashboard')">
                    <span class="icon">⊞</span>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item" data-tab="students" onclick="navigate('students')">
                    <span class="icon">👥</span>
                    <span>Students</span>
                </div>
                <div class="nav-item" data-tab="teachers" onclick="navigate('teachers')">
                    <span class="icon">🎓</span>
                    <span>Teachers</span>
                </div>
                <div class="nav-item" data-tab="classes" onclick="navigate('classes')">
                    <span class="icon">🏫</span>
                    <span>Classes</span>
                </div>
                <div class="nav-item" data-tab="attendance" onclick="navigate('attendance')">
                    <span class="icon">📅</span>
                    <span>Attendance</span>
                </div>
                <div class="nav-item active" data-tab="fees" onclick="navigate('fees')">
                    <span class="icon">💲</span>
                    <span>Fees</span>
                </div>
                <div class="nav-item" data-tab="homework" onclick="navigate('homework')">
                    <span class="icon">📖</span>
                    <span>Homework</span>
                </div>
                <div class="nav-item" data-tab="timetable" onclick="navigate('timetable')">
                    <span class="icon">⏰</span>
                    <span>Timetable</span>
                </div>
                <div class="nav-item" data-tab="notices" onclick="navigate('notices')">
                    <span class="icon">📢</span>
                    <span>Notice Board</span>
                </div>
                <div class="nav-item" data-tab="communication" onclick="navigate('communication')">
                    <span class="icon">💬</span>
                    <span>Communication</span>
                </div>
                <div class="nav-item" data-tab="reports" onclick="navigate('reports')">
                    <span class="icon">📊</span>
                    <span>Reports</span>
                </div>
                <div class="nav-item" data-tab="ai_assistant" onclick="navigate('ai_assistant')">
                    <span class="icon">✨</span>
                    <span>AI Assistance</span>
                </div>
                <div class="nav-item" data-tab="roles_permissions" onclick="navigate('roles_permissions')">
                    <span class="icon">🛡️</span>
                    <span>Roles & Permissions</span>
                </div>
                <div class="nav-item" data-tab="subject_class" onclick="navigate('subject_class')">
                    <span class="icon">🔀</span>
                    <span>Subject & Class</span>
                </div>
                <div class="nav-item" data-tab="tests_exams" onclick="navigate('tests_exams')">
                    <span class="icon">📝</span>
                    <span>Tests & Exams</span>
                </div>
                <div class="nav-item" data-tab="study_materials" onclick="navigate('study_materials')">
                    <span class="icon">📚</span>
                    <span>Study Materials</span>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="app-main">
            <!-- Top App Header -->
            <header class="app-header">
                <!-- Search Bar -->
                <div class="header-search">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Search students, classes, faculty..." />
                </div>

                <!-- Right Actions -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <!-- Theme Toggle -->
                    <button id="theme-btn" class="btn btn-secondary btn-sm" onclick="toggleTheme()" title="Toggle Theme" style="padding: 0.35rem 0.6rem; font-size: 0.95rem;">
                        🌙
                    </button>

                    <!-- Notifications Bell with Red Indicator -->
                    <div style="position: relative; cursor: pointer;" onclick="navigate('notices')">
                        <span style="font-size: 1.25rem;">🔔</span>
                        <span style="position: absolute; top: 0; right: 0; width: 7px; height: 7px; background: #EF4444; border-radius: 50%;"></span>
                    </div>

                    <!-- User Profile Wrapper with Dropdown Menu -->
                    <div class="user-profile-wrapper">
                        <div class="user-profile-pill" onclick="toggleUserMenu(event)">
                            <div class="user-avatar-circle" id="header-user-avatar">SA</div>
                            <div id="header-user-name" style="font-size: 0.8125rem; font-weight: 700; color: var(--text-main); padding-right: 0.3rem;">
                                Alflah (Principal)
                            </div>
                            <span style="font-size: 0.7rem; color: var(--text-light);">▼</span>
                        </div>

                        <!-- Dropdown Popover -->
                        <div class="user-dropdown-menu" id="user-dropdown-menu">
                            <div style="padding: 0.4rem 0.5rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 0.25rem;">
                                <div id="menu-user-name" style="font-weight: 800; font-size: 0.875rem; color: var(--text-main);">Alflah</div>
                                <div id="menu-user-role" style="font-size: 0.75rem; color: var(--brand-orange); font-weight: 600;">School Admin</div>
                                <div id="menu-school-name" style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.15rem;">Greenfield International</div>
                            </div>

                            <button class="dropdown-item" onclick="openLoginModal()">
                                <span>🔄</span> Switch Account / Sign In
                            </button>
                            <button class="dropdown-item dropdown-item-danger" onclick="handleLogout()">
                                <span>🚪</span> Sign Out / Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dynamic Viewport -->
            <main class="app-viewport" id="viewport">
                <!-- Injected via schoolos-app.js -->
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
