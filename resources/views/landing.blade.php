<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eschoolAI — Multi-Tenant SaaS School Management & AI Education Platform</title>
    <link rel="stylesheet" href="/css/schoolos-design-system.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --landing-bg: #0B0F19;
            --landing-card: #151D2E;
            --landing-border: rgba(255, 255, 255, 0.08);
            --landing-accent: #FF5B37;
            --landing-purple: #8B5CF6;
            --landing-indigo: #4F46E5;
        }

        body {
            background-color: var(--landing-bg);
            color: #F8FAFC;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            line-height: 1.6;
        }

        .landing-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--landing-border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links {
            display: flex;
            gap: 1.75rem;
            align-items: center;
        }

        .nav-links a {
            color: #94A3B8;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: color 0.15s ease;
        }

        .nav-links a:hover {
            color: #FFFFFF;
        }

        .hero-section {
            padding: 5.5rem 2rem 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            position: relative;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 91, 55, 0.12);
            border: 1px solid rgba(255, 91, 55, 0.3);
            color: #FF5B37;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-size: clamp(2.2rem, 5vw, 3.8rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, #FFFFFF 30%, #CBD5E1 70%, #FF5B37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.125rem;
            color: #94A3B8;
            max-width: 760px;
            margin: 0 auto 2.5rem auto;
        }

        .hero-cta-row {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 3.5rem;
        }

        .hero-btn-primary {
            background: linear-gradient(135deg, #FF5B37, #FF785A);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 0.85rem 1.75rem;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(255, 91, 55, 0.35);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .hero-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(255, 91, 55, 0.45);
        }

        .hero-btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #F8FAFC;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.85rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            border: 1px solid var(--landing-border);
            transition: background 0.15s ease;
        }

        .hero-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .preview-window {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            overflow: hidden;
            text-align: left;
        }

        .preview-topbar {
            background: #1F2937;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .traffic-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .section-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4.5rem 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-tag {
            color: #FF5B37;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.5rem;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
        }

        .section-desc {
            color: #94A3B8;
            font-size: 1rem;
            max-width: 650px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .feature-card {
            background: var(--landing-card);
            border: 1px solid var(--landing-border);
            border-radius: 14px;
            padding: 1.75rem;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 91, 55, 0.4);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(255, 91, 55, 0.12);
            color: #FF5B37;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 1.25rem;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .pricing-card {
            background: var(--landing-card);
            border: 1px solid var(--landing-border);
            border-radius: 16px;
            padding: 2.25rem;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .pricing-card.featured {
            border-color: #FF5B37;
            box-shadow: 0 0 30px rgba(255, 91, 55, 0.2);
        }

        .calculator-box {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.9));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            margin-top: 2rem;
        }

        /* Modal */
        .onboarding-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .onboarding-modal-backdrop.active {
            display: flex;
        }

        .onboarding-modal {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            width: 100%;
            max-width: 580px;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero-section { padding: 3rem 1rem 2rem 1rem; }
            .section-container { padding: 3rem 1rem; }
        }
    </style>
</head>
<body>

    <!-- Sticky Navigation Bar -->
    <header class="landing-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="brand-badge">eS</div>
            <div>
                <div style="font-weight: 800; font-size: 1.15rem; color: #FFFFFF; letter-spacing: -0.02em;">eschoolAI</div>
                <div style="font-size: 0.6875rem; color: #94A3B8; text-transform: uppercase; font-weight: 700;">Education SaaS</div>
            </div>
        </div>

        <nav class="nav-links">
            <a href="#features">Features</a>
            <a href="#ai-studio">AI Studio</a>
            <a href="#erp-modules">16 ERP Modules</a>
            <a href="#roi-calculator">ROI Calculator</a>
            <a href="#pricing">Pricing</a>
            <a href="/docs">Docs</a>
        </nav>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="/login" class="hero-btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8125rem;">Sign In</a>
            <button onclick="openOnboardingModal()" class="hero-btn-primary" style="padding: 0.5rem 1.15rem; font-size: 0.8125rem;">
                🚀 Start Free Trial
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-badge">
            ✨ Next-Gen Multi-Tenant AI Education Platform
        </div>
        <h1 class="hero-title">
            The Intelligent Operating System<br>for Modern SaaS Schools
        </h1>
        <p class="hero-subtitle">
            Manage student enrollment, fee collections, timetables, and examinations with precision — augmented with an embedded AI pedagogical copilot that cuts teacher lesson preparation time by 80%.
        </p>

        <div class="hero-cta-row">
            <button onclick="openOnboardingModal()" class="hero-btn-primary">
                ⚡ Launch 14-Day Free School Trial
            </button>
            <a href="/app" class="hero-btn-secondary">
                🖥️ Explore Live Interactive Portal
            </a>
        </div>

        <!-- Live Preview Mockup -->
        <div class="preview-window">
            <div class="preview-topbar">
                <div class="traffic-dot" style="background: #EF4444;"></div>
                <div class="traffic-dot" style="background: #F59E0B;"></div>
                <div class="traffic-dot" style="background: #10B981;"></div>
                <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: #94A3B8; margin-left: 0.5rem;">
                    https://greenfield.eschoolai.com/dashboard
                </span>
            </div>
            <div style="padding: 1.5rem; background: #0B0F19;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                    <div style="background: #1F2937; padding: 1rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);">
                        <div style="font-size: 0.75rem; color: #94A3B8;">Enrolled Students</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: #F8FAFC;">1,420</div>
                        <div style="font-size: 0.75rem; color: #10B981;">↑ +12% this term</div>
                    </div>
                    <div style="background: #1F2937; padding: 1rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);">
                        <div style="font-size: 0.75rem; color: #94A3B8;">Fee Realization</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: #F8FAFC;">₹94.2 Lakh</div>
                        <div style="font-size: 0.75rem; color: #10B981;">↑ 98.4% collected</div>
                    </div>
                    <div style="background: #1F2937; padding: 1rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);">
                        <div style="font-size: 0.75rem; color: #94A3B8;">AI Hours Saved</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: #FF5B37;">284 hrs/mo</div>
                        <div style="font-size: 0.75rem; color: #FF5B37;">✨ 12 Lesson Plans / wk</div>
                    </div>
                </div>
                <div style="background: #1E293B; border-radius: 10px; padding: 1rem; font-size: 0.8125rem; color: #CBD5E1; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="font-weight: 700; color: #FF5B37; margin-bottom: 0.35rem;">🤖 AI Lesson Plan Generator Output (Class 9 Physics):</div>
                    <div>• <strong>Hook:</strong> Faraday Induction Ring demonstration • <strong>Bloom's Taxonomy:</strong> Analysis & Synthesis • <strong>Quiz:</strong> 5 Formative Questions Generated.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Studio Section -->
    <section id="ai-studio" class="section-container" style="border-top: 1px solid var(--landing-border);">
        <div class="section-header">
            <div class="section-tag">Empower Educators</div>
            <h2 class="section-title">6 Built-in AI Modules in One Unified Studio</h2>
            <p class="section-desc">
                Never switch between ChatGPT, Claude, and multiple portals. All pedagogical generations run through a governed, multi-provider abstraction with real-time cost telemetry.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">✨</div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">AI Lesson Planner</h3>
                <p style="color: #94A3B8; font-size: 0.875rem;">Generates structured, standards-aligned lesson plans with 5E instructional models, real-world case studies, and formative rubrics.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Question Paper Builder</h3>
                <p style="color: #94A3B8; font-size: 0.875rem;">Builds balanced exam papers with Section A (MCQs), Section B (Short), and Section C (Long) adhering to Bloom's taxonomy.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📑</div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Worksheet & Quiz Synthesizer</h3>
                <p style="color: #94A3B8; font-size: 0.875rem;">Generates differentiated practice worksheets for tiered learning levels (Foundational, Intermediate, Advanced).</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">OCR Answer Sheet Evaluation</h3>
                <p style="color: #94A3B8; font-size: 0.875rem;">Ingests handwritten student submissions, extracts text via OCR, and produces qualitative rubric scoring cards in seconds.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📢</div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Formal Circular Generator</h3>
                <p style="color: #94A3B8; font-size: 0.875rem;">Synthesizes formal school announcements and circulars formatted in official letterhead ready for parent broadcast.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🧠</div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Tenant Vector RAG (Qdrant)</h3>
                <p style="color: #94A3B8; font-size: 0.875rem;">Upload proprietary textbooks and syllabi into an isolated Qdrant vector space. Guaranteed 0% cross-tenant data leakage.</p>
            </div>
        </div>
    </section>

    <!-- 16 Core ERP Modules Section -->
    <section id="erp-modules" class="section-container" style="border-top: 1px solid var(--landing-border);">
        <div class="section-header">
            <div class="section-tag">Comprehensive Campus Suite</div>
            <h2 class="section-title">16 Ready-to-Use Enterprise ERP Modules</h2>
            <p class="section-desc">Every aspect of school administration, from admission desks to financial ledgers, handled seamlessly.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">👥 Student Directory</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Realtime enrollment, roll numbers, and parent details.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">🎓 Faculty & Teachers</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Designation management, workloads, and staff records.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">🏫 Classes & Sections</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Grade structure hierarchies and section capacities.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">📅 Attendance Register</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Instant 1-click bulk daily marking and parent SMS alerts.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">💳 Fees & Invoicing</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Batch invoice generator, concessions, and instant receipts.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">📖 Homework & Tasks</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Digital homework posting, submissions, and grading.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">⏰ Timetable Matrix</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Weekly schedule planner for classes and faculties.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">📢 Notice Board</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Broadcast announcements targeted by student, teacher, or parent.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">💬 Live Messaging Hub</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Direct parent-teacher chat and emergency alerts.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">🔀 Subject Allocation</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Assign faculty and curriculum across sections.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">📝 Exams & Marks</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Mid-term and annual marksheet and GPA generation.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">📊 Reports & Audit</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">1-click CSV data streaming and FERPA compliance trails.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">🎨 Branding & Themes</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Custom institutional colors, crests, and dynamic styling.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">🛡️ RBAC Permission Matrix</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Fine-grained access control across 8 user roles.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">👑 Super Admin Control</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">Multi-tenant SaaS dashboard, MRR, and school telemetry.</div>
            </div>
            <div style="background: #111827; border: 1px solid rgba(255,255,255,0.06); padding: 1.25rem; border-radius: 12px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #F8FAFC; margin-bottom: 0.25rem;">📄 Word (.doc) Manual</div>
                <div style="font-size: 0.78rem; color: #94A3B8;">1-click master document export with full manual.</div>
            </div>
        </div>
    </section>

    <!-- Interactive ROI Calculator -->
    <section id="roi-calculator" class="section-container" style="border-top: 1px solid var(--landing-border);">
        <div class="section-header">
            <div class="section-tag">Calculate Value</div>
            <h2 class="section-title">Interactive School ROI & Savings Calculator</h2>
            <p class="section-desc">See how much time and operational costs eschoolAI saves your institution every month.</p>
        </div>

        <div class="calculator-box">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; align-items: center;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 0.5rem; font-size: 1rem;">
                        Number of Enrolled Students: <span id="calc-students-val" style="color: #FF5B37; font-weight: 800;">800</span>
                    </label>
                    <input type="range" id="calc-students-range" min="100" max="4000" step="50" value="800" oninput="updateRoiCalc(this.value)" style="width: 100%; accent-color: #FF5B37; height: 8px; cursor: pointer;" />

                    <div style="margin-top: 1.5rem;">
                        <div style="font-size: 0.8125rem; color: #94A3B8; margin-bottom: 0.25rem;">Recommended Subscription Tier:</div>
                        <div id="calc-plan-name" style="font-size: 1.25rem; font-weight: 800; color: #10B981;">Growth Tier ($79/mo)</div>
                    </div>
                </div>

                <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="color: #94A3B8; font-size: 0.875rem;">Faculty Hours Saved / Mo:</span>
                        <strong id="calc-hours-saved" style="color: #F8FAFC; font-size: 1.1rem;">160 hrs</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="color: #94A3B8; font-size: 0.875rem;">Fee Leakage Prevented:</span>
                        <strong id="calc-fee-saved" style="color: #10B981; font-size: 1.1rem;">₹1.2 Lakh / yr</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 0.75rem;">
                        <span style="color: #CBD5E1; font-weight: 700; font-size: 0.95rem;">Estimated Net Annual ROI:</span>
                        <strong id="calc-roi" style="color: #FF5B37; font-size: 1.35rem; font-weight: 800;">14.2x</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Plans Section -->
    <section id="pricing" class="section-container" style="border-top: 1px solid var(--landing-border);">
        <div class="section-header">
            <div class="section-tag">Predictable SaaS Pricing</div>
            <h2 class="section-title">Transparent Plans Built for Every School Size</h2>
            <p class="section-desc">All plans include a 14-day risk-free trial. No credit card required to start.</p>
        </div>

        <div class="pricing-grid">
            <div class="pricing-card">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem;">Starter Plan</h3>
                <p style="color: #94A3B8; font-size: 0.8125rem; margin-bottom: 1.5rem;">For small academies & tutoring centers.</p>
                <div style="font-size: 2.2rem; font-weight: 800; margin-bottom: 1.5rem;">$29 <span style="font-size: 0.9rem; color: #94A3B8; font-weight: normal;">/ month</span></div>
                <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; font-size: 0.84rem; color: #CBD5E1; line-height: 2;">
                    <li>✓ Up to 250 Enrolled Students</li>
                    <li>✓ Core 16 Academic & Fee ERP Modules</li>
                    <li>✓ 200 AI Generations / month</li>
                    <li>✓ Standard Email Support</li>
                </ul>
                <button onclick="openOnboardingModal('Starter')" class="hero-btn-secondary" style="margin-top: auto; text-align: center;">Start 14-Day Trial</button>
            </div>

            <div class="pricing-card featured">
                <div style="position: absolute; top: -12px; right: 24px; background: #FF5B37; color: #FFFFFF; font-size: 0.7rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 999px;">MOST POPULAR</div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem;">Growth Plan</h3>
                <p style="color: #94A3B8; font-size: 0.8125rem; margin-bottom: 1.5rem;">For growing K-12 schools & high schools.</p>
                <div style="font-size: 2.2rem; font-weight: 800; margin-bottom: 1.5rem;">$79 <span style="font-size: 0.9rem; color: #94A3B8; font-weight: normal;">/ month</span></div>
                <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; font-size: 0.84rem; color: #CBD5E1; line-height: 2;">
                    <li>✓ Up to 1,500 Enrolled Students</li>
                    <li>✓ All 16 Modules + Full AI Education Studio</li>
                    <li>✓ Tenant Vector Space (Qdrant RAG)</li>
                    <li>✓ 1,500 AI Generations / month</li>
                    <li>✓ Custom Branding & Theme Studio</li>
                    <li>✓ Priority 24/7 SLA Support</li>
                </ul>
                <button onclick="openOnboardingModal('Growth')" class="hero-btn-primary" style="margin-top: auto; text-align: center;">Start 14-Day Trial</button>
            </div>

            <div class="pricing-card">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem;">Enterprise Scale</h3>
                <p style="color: #94A3B8; font-size: 0.8125rem; margin-bottom: 1.5rem;">For school groups & multi-branch districts.</p>
                <div style="font-size: 2.2rem; font-weight: 800; margin-bottom: 1.5rem;">$199 <span style="font-size: 0.9rem; color: #94A3B8; font-weight: normal;">/ month</span></div>
                <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; font-size: 0.84rem; color: #CBD5E1; line-height: 2;">
                    <li>✓ Unlimited Students & Campuses</li>
                    <li>✓ Unlimited AI Token Processing</li>
                    <li>✓ Dedicated Database & Vector Cluster</li>
                    <li>✓ Custom Domain & White-Label Mobile App</li>
                    <li>✓ Dedicated Account Executive</li>
                </ul>
                <button onclick="openOnboardingModal('Enterprise')" class="hero-btn-secondary" style="margin-top: auto; text-align: center;">Contact Enterprise</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="border-top: 1px solid var(--landing-border); padding: 3rem 2rem; text-align: center; color: #64748B; font-size: 0.8125rem;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div class="brand-badge" style="width: 28px; height: 28px; font-size: 0.85rem;">eS</div>
                <span style="font-weight: 700; color: #F8FAFC;">eschoolAI</span> — Multi-Tenant SaaS School Operating System
            </div>
            <div>
                © 2026 eschoolAI Inc. All rights reserved. • FERPA & GDPR Compliant.
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="/docs" style="color: #94A3B8; text-decoration: none;">Documentation</a>
                <a href="/docs/download" style="color: #94A3B8; text-decoration: none;">Download Word Manual</a>
                <a href="/app" style="color: #FF5B37; text-decoration: none; font-weight: 700;">Open Portal</a>
            </div>
        </div>
    </footer>

    <!-- Interactive 4-Step School Onboarding Modal Wizard -->
    <div class="onboarding-modal-backdrop" id="onboardingModal" onclick="if(event.target===this) closeOnboardingModal()">
        <div class="onboarding-modal">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #FFFFFF;">🚀 Onboard Your School</h3>
                    <div style="font-size: 0.78rem; color: #94A3B8;">Provision your multi-tenant instance in 60 seconds</div>
                </div>
                <button onclick="closeOnboardingModal()" style="background: none; border: none; color: #94A3B8; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>

            <form onsubmit="handleOnboardSubmit(event)">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="color: #E2E8F0; font-size: 0.8125rem; font-weight: 600;">School / Institution Name</label>
                    <input type="text" id="ob-school-name" class="form-control" required placeholder="e.g. Oakridge International Academy" style="background: #1F2937; border-color: rgba(255,255,255,0.1); color: #FFF;" />
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" style="color: #E2E8F0; font-size: 0.8125rem; font-weight: 600;">Custom Tenant Subdomain</label>
                    <div style="display: flex; align-items: center; background: #1F2937; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding-right: 0.75rem;">
                        <input type="text" id="ob-subdomain" class="form-control" required placeholder="oakridge" style="background: transparent; border: none; color: #FFF;" />
                        <span style="font-size: 0.78rem; color: #94A3B8; font-family: 'JetBrains Mono', monospace;">.eschoolai.com</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0; font-size: 0.8125rem; font-weight: 600;">Principal / Admin Email</label>
                        <input type="email" class="form-control" required placeholder="principal@school.edu" style="background: #1F2937; border-color: rgba(255,255,255,0.1); color: #FFF;" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0; font-size: 0.8125rem; font-weight: 600;">Admin Password</label>
                        <input type="password" class="form-control" required value="password123" style="background: #1F2937; border-color: rgba(255,255,255,0.1); color: #FFF;" />
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" style="color: #E2E8F0; font-size: 0.8125rem; font-weight: 600;">Curriculum / Educational Board</label>
                    <select class="form-control" style="background: #1F2937; border-color: rgba(255,255,255,0.1); color: #FFF;">
                        <option>CBSE / State Curriculum (India)</option>
                        <option>Cambridge International / IGCSE (UK)</option>
                        <option>International Baccalaureate (IB)</option>
                        <option>US K-12 Common Core</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" onclick="closeOnboardingModal()" class="hero-btn-secondary" style="padding: 0.6rem 1rem;">Cancel</button>
                    <button type="submit" class="hero-btn-primary" style="padding: 0.6rem 1.5rem;">⚡ Provision School Workspace</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openOnboardingModal(tier) {
            document.getElementById('onboardingModal').classList.add('active');
        }

        function closeOnboardingModal() {
            document.getElementById('onboardingModal').classList.remove('active');
        }

        function handleOnboardSubmit(e) {
            e.preventDefault();
            const school = document.getElementById('ob-school-name').value;
            const sub = document.getElementById('ob-subdomain').value || 'myschool';
            
            alert(`🎉 Success! School tenant "${school}" provisioned at https://${sub}.eschoolai.com. Launching workspace...`);
            window.location.href = '/app';
        }

        function updateRoiCalc(val) {
            const students = parseInt(val);
            document.getElementById('calc-students-val').innerText = students.toLocaleString();

            const hours = Math.round(students * 0.2);
            document.getElementById('calc-hours-saved').innerText = `${hours} hrs / mo`;

            const feeSaved = (students * 150).toLocaleString('en-IN');
            document.getElementById('calc-fee-saved').innerText = `₹${feeSaved} / yr`;

            if (students <= 250) {
                document.getElementById('calc-plan-name').innerText = 'Starter Plan ($29/mo)';
            } else if (students <= 1500) {
                document.getElementById('calc-plan-name').innerText = 'Growth Plan ($79/mo)';
            } else {
                document.getElementById('calc-plan-name').innerText = 'Enterprise Scale ($199/mo)';
            }
        }
    </script>
</body>
</html>
