<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI SchoolOS — Design System & Component Showcase</title>
    <link rel="stylesheet" href="/css/schoolos-design-system.css">
    <script src="/js/schoolos-ui.js"></script>
    <style>
        .token-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: var(--space-3);
            margin-top: var(--space-3);
        }
        .color-swatch {
            height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: var(--shadow-xs);
        }
    </style>
</head>
<body>

<div class="app-shell">
    <!-- Left Navigation Sidebar -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <div class="brand-icon">✨</div>
            <div class="brand-text">AI SchoolOS</div>
        </div>

        <div class="sidebar-content">
            <div class="nav-section-title">Overview</div>
            <a href="#overview" class="nav-item active">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>

            <div class="nav-section-title">Academics</div>
            <a href="#students" class="nav-item">
                <span class="nav-icon">🎓</span>
                <span>Students</span>
                <span class="nav-badge badge-primary">1,245</span>
            </a>
            <a href="#teachers" class="nav-item">
                <span class="nav-icon">👩‍🏫</span>
                <span>Teachers</span>
            </a>
            <a href="#classes" class="nav-item">
                <span class="nav-icon">🏫</span>
                <span>Classes & Sections</span>
            </a>
            <a href="#attendance" class="nav-item">
                <span class="nav-icon">📅</span>
                <span>Attendance</span>
            </a>

            <div class="nav-section-title">✨ AI Education</div>
            <a href="#ai-assistant" class="nav-item">
                <span class="nav-icon">🤖</span>
                <span>AI Assistant</span>
                <span class="nav-badge badge-ai">NEW</span>
            </a>
            <a href="#lesson-planner" class="nav-item">
                <span class="nav-icon">📚</span>
                <span>Lesson Planner</span>
            </a>
            <a href="#question-gen" class="nav-item">
                <span class="nav-icon">📝</span>
                <span>Question Papers</span>
            </a>
            <a href="#evaluator" class="nav-item">
                <span class="nav-icon">📄</span>
                <span>Answer Evaluation</span>
            </a>

            <div class="nav-section-title">Finance & Admin</div>
            <a href="#finance" class="nav-item">
                <span class="nav-icon">💳</span>
                <span>Fees & Finance</span>
            </a>
            <a href="#settings" class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span>Settings</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="app-main">
        <!-- Top App Bar -->
        <header class="app-topbar">
            <div class="global-search">
                <span class="search-icon">🔍</span>
                <input type="text" id="global-search-input" placeholder="Search students, classes, invoices, lesson plans...">
            </div>

            <div style="display: flex; align-items: center; gap: var(--space-4);">
                <button class="btn btn-ai btn-sm" onclick="SchoolOSUI.toast('✨ AI Copilot Activated', 'info')">
                    ✨ AI Assistant
                </button>
                <div style="width: 1px; height: 24px; background: var(--border-color);"></div>
                <div style="display: flex; align-items: center; gap: var(--space-2);">
                    <div class="avatar">GA</div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700;">Greenfield Admin</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">School Administrator</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="app-content">
            <!-- Breadcrumbs -->
            <div class="breadcrumbs">
                <a href="/">Home</a>
                <span class="separator">/</span>
                <a href="#">Design System</a>
                <span class="separator">/</span>
                <span style="color: var(--text-primary); font-weight: 600;">Component Showcase</span>
            </div>

            <div style="margin-bottom: var(--space-6);">
                <h1 style="font-size: 1.8rem; font-weight: 800;">Design System & Component Showcase</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Canonical library of modern, responsive SaaS components for AI SchoolOS.</p>
            </div>

            <!-- 1. Design Tokens -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">1. Color Tokens & Theme Palettes</div>
                        <div class="card-subtitle">Harmonious, calibrated color tokens rooted in CSS variables.</div>
                    </div>
                </div>
                <div class="token-grid">
                    <div class="color-swatch" style="background: var(--color-primary);">Primary</div>
                    <div class="color-swatch" style="background: var(--color-secondary);">Secondary</div>
                    <div class="color-swatch" style="background: var(--color-ai-purple);">AI Violet</div>
                    <div class="color-swatch" style="background: var(--color-success);">Success</div>
                    <div class="color-swatch" style="background: var(--color-warning);">Warning</div>
                    <div class="color-swatch" style="background: var(--color-danger);">Danger</div>
                    <div class="color-swatch" style="background: var(--text-primary);">Slate 900</div>
                </div>
            </div>

            <!-- 2. Stat Cards -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Enrolled Students</div>
                    <div class="stat-value">1,245</div>
                    <div class="stat-trend" style="color: var(--color-success);">↑ +8.4% from last term</div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-label">Daily Attendance Rate</div>
                    <div class="stat-value">94.2%</div>
                    <div class="stat-trend" style="color: var(--color-success);">✓ 1,170 Present Today</div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-label">Fee Collection (MTD)</div>
                    <div class="stat-value">$84,200</div>
                    <div class="stat-trend" style="color: var(--color-warning);">⚠ $21,800 Outstanding</div>
                </div>

                <div class="stat-card stat-ai">
                    <div class="stat-label">AI Generation Units</div>
                    <div class="stat-value">7,450</div>
                    <div class="stat-trend" style="color: var(--color-ai-purple);">✨ 74.5% of Monthly Quota</div>
                </div>
            </div>

            <!-- 3. AI Specialized Components -->
            <div class="ai-card" style="margin-bottom: var(--space-6);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                    <div style="display: flex; align-items: center; gap: var(--space-2);">
                        <span class="badge badge-ai">✨ AI RAG Module</span>
                        <h2 style="font-size: 1.25rem; font-weight: 700;">Grounded AI Lesson Planner</h2>
                    </div>
                    <span class="badge badge-success">✓ 94% Match Confidence</span>
                </div>

                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: var(--space-4);">
                    Generated structured pedagogy plan for <strong>Class 8 Science: Chapter 4 — Force and Friction</strong>.
                </p>

                <div style="background: var(--bg-surface); padding: var(--space-4); border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: var(--space-4);">
                    <h4 style="font-size: 0.95rem; margin-bottom: var(--space-2);">Key Pedagogical Activities:</h4>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">
                        1. Spring balance demonstration on rough vs polished wood surfaces. <span class="ai-citation">📖 Science Class 8, p.42</span><br>
                        2. Classroom discussion on gravitational friction and atmospheric resistance. <span class="ai-citation">📖 Science Class 8, p.45</span>
                    </p>
                </div>

                <div style="display: flex; gap: var(--space-2);">
                    <button class="btn btn-ai btn-sm" onclick="SchoolOSUI.toast('✨ Regenerating Lesson Plan...', 'info')">Regenerate</button>
                    <button class="btn btn-outline btn-sm" onclick="SchoolOSUI.toast('Exported PDF', 'success')">Export PDF</button>
                    <button class="btn btn-outline btn-sm">Assign to Class</button>
                </div>
            </div>

            <!-- 4. Interactive Tabs & Data Table -->
            <div class="card">
                <div class="tabs-nav">
                    <button class="tab-btn active" data-target="tab-students">Student Directory</button>
                    <button class="tab-btn" data-target="tab-forms">Interactive Form Elements</button>
                    <button class="tab-btn" data-target="tab-states">Component States (Loading/Empty/Error)</button>
                    <button class="tab-btn" data-target="tab-dialogs">Modals & Notifications</button>
                </div>

                <!-- Tab 1: Table -->
                <div class="tab-pane" id="tab-students">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4);">
                        <div style="display: flex; gap: var(--space-2);">
                            <input type="text" class="input" placeholder="Filter by student name..." style="width: 220px;">
                            <select class="select" style="width: 140px;">
                                <option>All Classes</option>
                                <option>Class 8-A</option>
                                <option>Class 9-B</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="SchoolOSUI.openModal('demo-modal')">+ Add New Student</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Admission #</th>
                                    <th>Student Name</th>
                                    <th>Class & Sec</th>
                                    <th>Attendance</th>
                                    <th>Fee Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>ADM-2026-084</code></td>
                                    <td><strong>Alex Miller</strong></td>
                                    <td>Class 8-A</td>
                                    <td><span class="badge badge-success">96% Present</span></td>
                                    <td><span class="badge badge-success">Paid</span></td>
                                    <td><button class="btn btn-outline btn-sm">View Profile</button></td>
                                </tr>
                                <tr>
                                    <td><code>ADM-2026-085</code></td>
                                    <td><strong>Sophia Chen</strong></td>
                                    <td>Class 8-A</td>
                                    <td><span class="badge badge-warning">82% Present</span></td>
                                    <td><span class="badge badge-danger">Due: $450</span></td>
                                    <td><button class="btn btn-outline btn-sm">View Profile</button></td>
                                </tr>
                                <tr>
                                    <td><code>ADM-2026-086</code></td>
                                    <td><strong>David Kumar</strong></td>
                                    <td>Class 9-B</td>
                                    <td><span class="badge badge-success">98% Present</span></td>
                                    <td><span class="badge badge-success">Paid</span></td>
                                    <td><button class="btn btn-outline btn-sm">View Profile</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Forms -->
                <div class="tab-pane" id="tab-forms" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="input" placeholder="e.g. Eleanor Vance">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="input" placeholder="e.g. eleanor@school.edu">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Class Allocation</label>
                            <select class="select">
                                <option>Class 8 (Science & Mathematics)</option>
                                <option>Class 9 (Physics)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select class="select">
                                <option>Teacher</option>
                                <option>School Admin</option>
                                <option>Accountant</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: States -->
                <div class="tab-pane" id="tab-states" style="display: none;">
                    <h3 style="font-size: 1rem; margin-bottom: var(--space-3);">Loading Skeletons</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--space-2); margin-bottom: var(--space-6);">
                        <div class="skeleton" style="height: 24px; width: 40%;"></div>
                        <div class="skeleton" style="height: 16px; width: 90%;"></div>
                        <div class="skeleton" style="height: 16px; width: 75%;"></div>
                    </div>

                    <h3 style="font-size: 1rem; margin-bottom: var(--space-3);">Empty State Placeholder</h3>
                    <div class="state-box" style="margin-bottom: var(--space-6);">
                        <div class="state-icon">📂</div>
                        <h4 style="font-size: 1.1rem;">No Exam Results Published Yet</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: var(--space-4);">Upload answer sheets or enter marks to generate the result ledger.</p>
                        <button class="btn btn-primary btn-sm">+ Create Examination</button>
                    </div>
                </div>

                <!-- Tab 4: Modals & Toasts -->
                <div class="tab-pane" id="tab-dialogs" style="display: none;">
                    <div style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
                        <button class="btn btn-primary" onclick="SchoolOSUI.openModal('demo-modal')">Open Test Modal</button>
                        <button class="btn btn-outline" onclick="SchoolOSUI.toast('Operation completed successfully!', 'success')">Trigger Success Toast</button>
                        <button class="btn btn-outline" onclick="SchoolOSUI.toast('Warning: Student attendance below 75%', 'warning')">Trigger Warning Toast</button>
                        <button class="btn btn-outline" onclick="SchoolOSUI.toast('Error: Cross-tenant query blocked', 'danger')">Trigger Error Toast</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Reusable Modal Component -->
<div class="modal-backdrop" id="demo-modal">
    <div class="modal-dialog">
        <div style="padding: var(--space-6); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.15rem; font-weight: 700;">Add Student Record</h3>
            <button class="btn btn-outline btn-sm" onclick="SchoolOSUI.closeModal('demo-modal')">✕</button>
        </div>
        <div style="padding: var(--space-6);">
            <div class="form-group">
                <label class="form-label">Student Name</label>
                <input type="text" class="input" placeholder="Full legal name">
            </div>
            <div class="form-group">
                <label class="form-label">Admission Number</label>
                <input type="text" class="input" value="ADM-2026-087" readonly style="background: var(--bg-page);">
            </div>
        </div>
        <div style="padding: var(--space-4) var(--space-6); background: var(--bg-page); border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: var(--space-2);">
            <button class="btn btn-outline" onclick="SchoolOSUI.closeModal('demo-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="SchoolOSUI.closeModal('demo-modal'); SchoolOSUI.toast('Student saved successfully!', 'success');">Save Student</button>
        </div>
    </div>
</div>

</body>
</html>
