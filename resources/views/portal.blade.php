<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eschoolAI — Multi-Role SaaS Enterprise Portal</title>
    <link rel="stylesheet" href="/css/schoolos-design-system.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --portal-sidebar-w: 260px;
        }
        body {
            margin: 0;
            background-color: var(--bg-primary, #090d16);
            color: var(--text-primary, #f8fafc);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .portal-layout {
            display: flex;
            width: 100%;
            height: 100%;
        }
        .portal-sidebar {
            width: var(--portal-sidebar-w);
            background: rgba(15, 23, 42, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            padding: 1.25rem;
            box-sizing: border-box;
        }
        .portal-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(139, 92, 246, 0.05) 0%, transparent 40%);
        }
        .role-pill {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #94a3b8;
            border: 1px solid transparent;
        }
        .role-pill:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
        }
        .role-pill.active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, 0.4);
            font-weight: 600;
        }
        .metric-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="portal-layout">
        <!-- Sidebar -->
        <aside class="portal-sidebar">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(99,102,241,0.4);">
                    eS
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 1rem; letter-spacing: -0.02em;">eschoolAI</div>
                    <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Enterprise SaaS</div>
                </div>
            </div>

            <div style="font-size: 0.6875rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 0.5rem; padding-left: 0.5rem;">
                Switch Role View
            </div>

            <div id="role-selector">
                <div class="role-pill active" onclick="switchRole('super_admin')">
                    <span>👑</span> Platform Super Admin
                </div>
                <div class="role-pill" onclick="switchRole('school_admin')">
                    <span>🏫</span> School Admin / Principal
                </div>
                <div class="role-pill" onclick="switchRole('teacher')">
                    <span>👩‍🏫</span> Teacher Dashboard
                </div>
                <div class="role-pill" onclick="switchRole('student')">
                    <span>🎓</span> Student & Parent Portal
                </div>
            </div>

            <div style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="/docs" style="color: #94a3b8; text-decoration: none; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.5rem; border-radius: 6px;">
                    <span>📘</span> API Reference
                </a>
                <a href="/showcase" style="color: #94a3b8; text-decoration: none; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.5rem; border-radius: 6px;">
                    <span>🎨</span> UI Component Kit
                </a>
                <a href="/healthz" style="color: #10b981; text-decoration: none; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.5rem; border-radius: 6px;">
                    <span>⚡</span> System: Online
                </a>
            </div>
        </aside>

        <!-- Main Content View -->
        <main class="portal-main">
            <!-- Header -->
            <header style="padding: 1.25rem 2rem; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 id="view-title" style="margin: 0; font-size: 1.35rem; font-weight: 700;">Platform Super Admin Console</h1>
                    <p id="view-subtitle" style="margin: 0.2rem 0 0 0; font-size: 0.8125rem; color: #94a3b8;">Multi-tenant cluster analytics, MRR telemetry, and global AI infrastructure</p>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <div style="padding: 0.4rem 0.8rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 9999px; font-size: 0.75rem; color: #34d399; font-weight: 600; display: flex; align-items: center; gap: 0.4rem;">
                        <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></span>
                        Tenant: Greenfield Academy
                    </div>
                </div>
            </header>

            <!-- Dynamic View Container -->
            <div id="view-content" style="padding: 2rem; display: flex; flex-direction: column; gap: 1.75rem;">
                <!-- Content injected via JS based on active role -->
            </div>
        </main>
    </div>

    <script>
        const views = {
            super_admin: {
                title: "Platform Super Admin Console",
                subtitle: "Multi-tenant cluster analytics, MRR telemetry, and global AI infrastructure",
                metrics: [
                    { label: "Active School Tenants", val: "1,248", delta: "+12% this month", color: "#6366f1" },
                    { label: "Monthly Recurring Revenue", val: "$184,200", delta: "+8.4% MRR growth", color: "#10b981" },
                    { label: "AI Tokens Processed", val: "48.2M", delta: "0.042 avg cost/req", color: "#8b5cf6" },
                    { label: "Platform Uptime", val: "99.98%", delta: "55/55 health probes", color: "#06b6d4" }
                ],
                panels: `
                    <div class="grid-2">
                        <div class="metric-card">
                            <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">Global AI Provider Load Balance</div>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.3rem;">
                                        <span>OpenAI GPT-4o</span>
                                        <span style="color: #6366f1; font-weight: 600;">54% traffic ($0.005/k)</span>
                                    </div>
                                    <div style="background: rgba(255,255,255,0.06); height: 6px; border-radius: 3px; overflow: hidden;"><div style="width: 54%; background: #6366f1; height: 100%;"></div></div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.3rem;">
                                        <span>Google Gemini 1.5 Pro</span>
                                        <span style="color: #10b981; font-weight: 600;">28% traffic ($0.0035/k)</span>
                                    </div>
                                    <div style="background: rgba(255,255,255,0.06); height: 6px; border-radius: 3px; overflow: hidden;"><div style="width: 28%; background: #10b981; height: 100%;"></div></div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.3rem;">
                                        <span>Anthropic Claude 3.5 Sonnet</span>
                                        <span style="color: #f59e0b; font-weight: 600;">14% traffic ($0.015/k)</span>
                                    </div>
                                    <div style="background: rgba(255,255,255,0.06); height: 6px; border-radius: 3px; overflow: hidden;"><div style="width: 14%; background: #f59e0b; height: 100%;"></div></div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.3rem;">
                                        <span>Ollama Self-Hosted (Llama 3)</span>
                                        <span style="color: #06b6d4; font-weight: 600;">4% traffic ($0.000/k)</span>
                                    </div>
                                    <div style="background: rgba(255,255,255,0.06); height: 6px; border-radius: 3px; overflow: hidden;"><div style="width: 4%; background: #06b6d4; height: 100%;"></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">Security & Compliance</div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.8125rem;">
                                <div style="display: flex; justify-content: space-between; padding: 0.5rem; background: rgba(255,255,255,0.03); border-radius: 6px;">
                                    <span>FERPA Data Exports</span>
                                    <span style="color: #34d399; font-weight: 600;">Compliant</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 0.5rem; background: rgba(255,255,255,0.03); border-radius: 6px;">
                                    <span>Immutable Audit Trails</span>
                                    <span style="color: #34d399; font-weight: 600;">Active (AES-256)</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 0.5rem; background: rgba(255,255,255,0.03); border-radius: 6px;">
                                    <span>Automated Backups</span>
                                    <span style="color: #34d399; font-weight: 600;">SHA256 Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `
            },
            school_admin: {
                title: "Greenfield Academy — Executive Admin Dashboard",
                subtitle: "Realtime school attendance, fee receipts, examination schedules, and teacher assignments",
                metrics: [
                    { label: "Enrolled Students", val: "842", delta: "Class 1 to 12", color: "#6366f1" },
                    { label: "Daily Attendance", val: "96.4%", delta: "+1.2% vs yesterday", color: "#10b981" },
                    { label: "Fee Collection (MTD)", val: "$64,250", delta: "$4,100 balance due", color: "#f59e0b" },
                    { label: "AI Quota Consumption", val: "18.4%", delta: "2M limit / month", color: "#8b5cf6" }
                ],
                panels: `
                    <div class="grid-2">
                        <div class="metric-card">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <div style="font-weight: 600; font-size: 0.95rem;">School Academic Operations</div>
                                <button class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;" onclick="triggerEmergencyAlert()">🚨 Emergency Alert</button>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.8125rem;">
                                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                                    <span>Term 1 Mid-Year Examination</span>
                                    <span style="color: #60a5fa; font-weight: 600;">Scheduled Oct 15</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                                    <span>Annual Science Fair Notice</span>
                                    <span style="color: #34d399; font-weight: 600;">Dispatched to Notice Board</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span>Grade 10 Batch Invoicing</span>
                                    <span style="color: #f59e0b; font-weight: 600;">32 / 35 Invoices Settled</span>
                                </div>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">Quick System Actions</div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="/api/v1/exports/students" class="btn" style="background: rgba(255,255,255,0.06); color: white; text-align: center; text-decoration: none; padding: 0.5rem; font-size: 0.8rem; border-radius: 6px;">📥 Export Student Roster CSV</a>
                                <a href="/api/v1/exports/fees" class="btn" style="background: rgba(255,255,255,0.06); color: white; text-align: center; text-decoration: none; padding: 0.5rem; font-size: 0.8rem; border-radius: 6px;">📥 Export Fee Ledger CSV</a>
                                <a href="/api/v1/exports/attendance" class="btn" style="background: rgba(255,255,255,0.06); color: white; text-align: center; text-decoration: none; padding: 0.5rem; font-size: 0.8rem; border-radius: 6px;">📥 Export Attendance Register</a>
                            </div>
                        </div>
                    </div>
                `
            },
            teacher: {
                title: "Educator & Teacher Studio",
                subtitle: "AI lesson planning, question paper generator, and student grading workbench",
                metrics: [
                    { label: "My Classes", val: "4 Sections", delta: "Grade 8, 9, 10", color: "#6366f1" },
                    { label: "Active Homework", val: "6 Due", delta: "142 submissions reviewed", color: "#10b981" },
                    { label: "AI Lesson Plans", val: "24 Created", delta: "Bloom's taxonomy aligned", color: "#8b5cf6" },
                    { label: "Pending Evaluations", val: "3 Scripts", delta: "OCR ready", color: "#f59e0b" }
                ],
                panels: `
                    <div class="grid-2">
                        <div class="metric-card">
                            <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">✨ AI Pedagogical Copilot</div>
                            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                                <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8125rem;">+ Generate Lesson Plan</button>
                                <button class="btn" style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); padding: 0.5rem 1rem; font-size: 0.8125rem;">+ Synthesize Question Paper</button>
                                <button class="btn" style="background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); padding: 0.5rem 1rem; font-size: 0.8125rem;">+ Create Multi-tier Worksheet</button>
                            </div>
                            <div style="margin-top: 1rem; background: rgba(0,0,0,0.2); padding: 0.75rem; border-radius: 8px; font-size: 0.75rem; font-family: 'JetBrains Mono', monospace; color: #94a3b8;">
                                <span style="color: #818cf8;">AI Prompt Template:</span> lesson_planner.v1 (Bloom's Revised Taxonomy)
                            </div>
                        </div>
                        <div class="metric-card">
                            <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">Today's Teaching Schedule</div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.8rem;">
                                <div style="display: flex; justify-content: space-between; padding: 0.4rem; background: rgba(255,255,255,0.03); border-radius: 6px;">
                                    <span>09:00 AM — Grade 8A</span>
                                    <span style="color: #a5b4fc;">Physics (Electromagnetism)</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 0.4rem; background: rgba(255,255,255,0.03); border-radius: 6px;">
                                    <span>11:15 AM — Grade 10B</span>
                                    <span style="color: #a5b4fc;">Mathematics (Calculus)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `
            },
            student: {
                title: "Student & Parent Academic Portal",
                subtitle: "Daily schedule, homework assignments, consolidated report card, and fee payment receipts",
                metrics: [
                    { label: "My Attendance", val: "98.2%", delta: "0 unexcused absences", color: "#10b981" },
                    { label: "Current GPA", val: "3.85 / 4.0", delta: "Grade A+ Honors Track", color: "#6366f1" },
                    { label: "Pending Homework", val: "2 Tasks", delta: "Due tomorrow", color: "#f59e0b" },
                    { label: "Fee Dues", val: "$0.00", delta: "Fully settled (Receipt REC-849)", color: "#06b6d4" }
                ],
                panels: `
                    <div class="grid-2">
                        <div class="metric-card">
                            <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">Active Homework & Assignments</div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.8125rem;">
                                <div style="display: flex; justify-content: space-between; padding: 0.5rem; background: rgba(255,255,255,0.03); border-radius: 6px;">
                                    <div>
                                        <div style="font-weight: 600; color: #f8fafc;">Science: Electromagnetic Induction Worksheet</div>
                                        <div style="font-size: 0.75rem; color: #94a3b8;">Due Monday, 11:59 PM</div>
                                    </div>
                                    <button class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Submit Online</button>
                                </div>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem;">Report Card Summary</div>
                            <div style="font-size: 0.8125rem; display: flex; flex-direction: column; gap: 0.4rem;">
                                <div style="display: flex; justify-content: space-between;"><span>Mathematics:</span><span style="color: #10b981; font-weight: 600;">94/100 (A+)</span></div>
                                <div style="display: flex; justify-content: space-between;"><span>Physics:</span><span style="color: #10b981; font-weight: 600;">91/100 (A+)</span></div>
                                <div style="display: flex; justify-content: space-between;"><span>English:</span><span style="color: #10b981; font-weight: 600;">88/100 (A)</span></div>
                            </div>
                        </div>
                    </div>
                `
            }
        };

        function switchRole(roleKey) {
            document.querySelectorAll('.role-pill').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');

            const view = views[roleKey];
            document.getElementById('view-title').innerText = view.title;
            document.getElementById('view-subtitle').innerText = view.subtitle;

            let metricsHtml = '<div class="grid-4">';
            view.metrics.forEach(m => {
                metricsHtml += `
                    <div class="metric-card">
                        <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 600; letter-spacing: 0.04em;">${m.label}</div>
                        <div style="font-size: 1.85rem; font-weight: 800; color: ${m.color}; letter-spacing: -0.03em;">${m.val}</div>
                        <div style="font-size: 0.75rem; color: #64748b;">${m.delta}</div>
                    </div>
                `;
            });
            metricsHtml += '</div>';

            document.getElementById('view-content').innerHTML = metricsHtml + view.panels;
        }

        function triggerEmergencyAlert() {
            alert("Emergency broadcast triggered! Realtime WebSocket event dispatched to all student, teacher, and parent channels.");
        }

        // Initialize default view
        switchRole('super_admin');
    </script>
</body>
</html>
