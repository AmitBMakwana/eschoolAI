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
            --landing-accent-hover: #E04824;
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
            -webkit-font-smoothing: antialiased;
        }

        /* 1. NAVIGATION */
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

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            color: #94A3B8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .nav-links a:hover {
            color: #FFFFFF;
        }

        .nav-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #F8FAFC;
            border: 1px solid var(--landing-border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-primary {
            background: var(--landing-accent);
            color: #FFFFFF;
            border: none;
        }

        .btn-primary:hover {
            background: var(--landing-accent-hover);
            transform: translateY(-1px);
        }

        /* Mobile Hamburger */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #FFF;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* 2. HERO */
        .hero-section {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-content {
            text-align: left;
        }

        .eyebrow {
            display: inline-block;
            background: rgba(255, 91, 55, 0.15);
            color: var(--landing-accent);
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 91, 55, 0.3);
        }

        .hero-title {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #FFF 30%, #94A3B8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: #94A3B8;
            margin-bottom: 2.5rem;
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
        }

        .hero-visual {
            background: var(--landing-card);
            border: 1px solid var(--landing-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            position: relative;
        }

        .dashboard-mockup {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .mockup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--landing-border);
        }

        .mockup-metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .mockup-metric-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--landing-border);
            padding: 1rem;
            border-radius: 8px;
        }

        .mockup-metric-title {
            font-size: 0.75rem;
            color: #94A3B8;
            margin-bottom: 0.5rem;
        }
        
        .mockup-metric-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #FFF;
        }

        .mockup-ai-callout {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(79, 70, 229, 0.1));
            border: 1px solid rgba(139, 92, 246, 0.3);
            padding: 1.25rem;
            border-radius: 8px;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .mockup-ai-icon {
            background: var(--landing-purple);
            color: #FFF;
            padding: 0.5rem;
            border-radius: 6px;
        }

        /* 3. FEATURE GRID */
        .section-container {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-title {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #FFF;
        }

        .section-subtitle {
            color: #94A3B8;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .feature-card {
            background: var(--landing-card);
            border: 1px solid var(--landing-border);
            padding: 2rem;
            border-radius: 12px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            border-color: rgba(255,255,255,0.2);
        }

        .feature-icon {
            font-size: 1.5rem;
            color: var(--landing-accent);
            margin-bottom: 1.25rem;
            background: rgba(255, 91, 55, 0.1);
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #FFF;
        }

        .feature-desc {
            font-size: 0.9rem;
            color: #94A3B8;
            line-height: 1.5;
        }

        .feature-more {
            text-align: center;
        }

        /* 4. WHY US */
        .why-section {
            background: #111827;
            border-top: 1px solid var(--landing-border);
            border-bottom: 1px solid var(--landing-border);
        }

        .why-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .why-content h2 {
            font-size: 2.25rem;
            margin-bottom: 1.5rem;
            color: #FFF;
        }

        .why-content p {
            color: #94A3B8;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .check-list {
            list-style: none;
            padding: 0;
        }

        .check-list li {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
            color: #F8FAFC;
        }

        .check-icon {
            color: #10B981;
            margin-top: 0.2rem;
        }
        
        .why-visual {
            background: var(--landing-card);
            border: 1px solid var(--landing-border);
            border-radius: 16px;
            padding: 3rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .stat-box {
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--landing-accent);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #94A3B8;
            font-weight: 500;
        }

        /* 5. PRICING */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .pricing-card {
            background: var(--landing-card);
            border: 1px solid var(--landing-border);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .pricing-card.popular {
            border: 2px solid var(--landing-accent);
            transform: scale(1.02);
        }

        .popular-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--landing-accent);
            color: #FFF;
            padding: 0.25rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .plan-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #FFF;
            margin-bottom: 1rem;
        }

        .plan-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: #FFF;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
        }
        
        .plan-period {
            font-size: 1rem;
            color: #94A3B8;
            font-weight: 500;
        }

        .plan-desc {
            color: #94A3B8;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            flex-grow: 1;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin-bottom: 2.5rem;
        }

        .plan-features li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #CBD5E1;
        }

        .custom-banner {
            background: linear-gradient(135deg, var(--landing-card), #1F2937);
            border: 1px solid var(--landing-border);
            border-radius: 12px;
            padding: 2rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .custom-banner-info h3 {
            font-size: 1.25rem;
            color: #FFF;
            margin-bottom: 0.5rem;
        }
        
        .custom-banner-info p {
            color: #94A3B8;
            font-size: 0.95rem;
        }

        /* 6. CONTACT */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .contact-card {
            background: var(--landing-card);
            border: 1px solid var(--landing-border);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }

        .contact-icon {
            font-size: 1.75rem;
            color: var(--landing-accent);
            margin-bottom: 1rem;
        }
        
        .contact-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #FFF;
            margin-bottom: 0.5rem;
        }
        
        .contact-detail {
            color: #94A3B8;
            font-size: 0.95rem;
        }

        /* 8. FOOTER */
        .landing-footer {
            background: #090C14;
            border-top: 1px solid var(--landing-border);
            padding: 4rem 2rem 2rem 2rem;
        }

        .footer-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 4rem;
            margin-bottom: 4rem;
        }

        .footer-brand p {
            color: #94A3B8;
            font-size: 0.9rem;
            margin-top: 1rem;
            max-width: 250px;
        }

        .footer-heading {
            color: #FFF;
            font-weight: 600;
            margin-bottom: 1.25rem;
            font-size: 1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: #94A3B8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #FFF;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            border-top: 1px solid var(--landing-border);
            padding-top: 2rem;
            text-align: center;
            color: #64748B;
            font-size: 0.85rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-section, .why-grid {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 3rem;
            }
            .hero-content {
                text-align: center;
            }
            .hero-actions {
                justify-content: center;
            }
            .check-list li {
                justify-content: center;
                text-align: left;
            }
            .custom-banner {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .feature-grid, .pricing-grid, .contact-grid, .footer-grid {
                grid-template-columns: 1fr;
            }
            .nav-links {
                display: none;
            }
            .mobile-menu-btn {
                display: block;
            }
            .nav-actions {
                display: none;
            }
            .hero-title {
                font-size: 2.25rem;
            }
        }
    </style>
</head>
<body>

    <!-- 1. NAVIGATION -->
    <header class="landing-header">
        <a href="/" class="brand-logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--landing-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
            eschoolAI
        </a>
        
        <nav class="nav-links">
            <a href="/">Home</a>
            <a href="#features">Features</a>
            <a href="#pricing">Pricing</a>
            <a href="#contact">Contact</a>
        </nav>
        
        <div class="nav-actions">
            <a href="/login" class="btn btn-secondary">Login</a>
            <a href="/login?intent=trial&utm_source=nav" class="btn btn-primary">Start trial</a>
        </div>
        
        <button class="mobile-menu-btn">☰</button>
    </header>

    <!-- 2. HERO -->
    <section class="hero-section">
        <div class="hero-content">
            <span class="eyebrow">AI SchoolOS</span>
            <h1 class="hero-title">Run your entire school effortlessly on one platform.</h1>
            <p class="hero-subtitle">Unify administration, empower teachers with AI, and keep parents in the loop. The complete ERP and AI learning suite built for forward-thinking schools.</p>
            <div class="hero-actions">
                <a href="/login?intent=register&utm_source=hero" class="btn btn-primary">Register your school</a>
                <a href="#contact" class="btn btn-secondary">Book a demo</a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="dashboard-mockup">
                <div class="mockup-header">
                    <div style="display:flex; gap:0.5rem;">
                        <div style="width:12px; height:12px; border-radius:50%; background:#EF4444;"></div>
                        <div style="width:12px; height:12px; border-radius:50%; background:#F59E0B;"></div>
                        <div style="width:12px; height:12px; border-radius:50%; background:#10B981;"></div>
                    </div>
                    <div style="color:#94A3B8; font-size:0.8rem;">SchoolOS Dashboard</div>
                </div>
                <div class="mockup-metrics">
                    <div class="mockup-metric-card">
                        <div class="mockup-metric-title">Total Students</div>
                        <div class="mockup-metric-value">1,248</div>
                    </div>
                    <div class="mockup-metric-card">
                        <div class="mockup-metric-title">Attendance</div>
                        <div class="mockup-metric-value">96.4%</div>
                    </div>
                    <div class="mockup-metric-card">
                        <div class="mockup-metric-title">Revenue (MTD)</div>
                        <div class="mockup-metric-value">$42,500</div>
                    </div>
                </div>
                <div class="mockup-ai-callout">
                    <div class="mockup-ai-icon">✨</div>
                    <div>
                        <div style="color:#FFF; font-weight:600; font-size:0.95rem; margin-bottom:0.25rem;">AI Lesson Planner</div>
                        <div style="color:#94A3B8; font-size:0.85rem;">Generated a comprehensive 5-day science lesson plan aligned with grade 8 standards in 4.2 seconds.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. FEATURE GRID -->
    <section id="features" class="section-container">
        <div class="section-header">
            <h2 class="section-title">Everything you need. Nothing you don't.</h2>
            <p class="section-subtitle">A meticulously crafted suite of modules to handle every aspect of school operations, augmented by intelligent AI workflows.</p>
        </div>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">👩‍🎓</div>
                <h3 class="feature-title">Student Management</h3>
                <p class="feature-desc">Comprehensive student profiles, digital enrollment, and complete longitudinal academic histories.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3 class="feature-title">Academics & Timetable</h3>
                <p class="feature-desc">Dynamic class scheduling, subject allocation, and conflict-free automated timetabling.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👨‍🏫</div>
                <h3 class="feature-title">Teacher Management</h3>
                <p class="feature-desc">Staff records, workload balancing, payroll integrations, and substitute teacher routing.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3 class="feature-title">Smart Attendance</h3>
                <p class="feature-desc">Rapid bulk attendance marking, real-time absence alerts to parents, and term-wise analytics.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3 class="feature-title">Exam & Results</h3>
                <p class="feature-desc">Custom grading scales, secure mark entry, and automated beautiful report card generation.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3 class="feature-title">Fee Engine</h3>
                <p class="feature-desc">Automated batch invoicing, custom fee heads, online payment collection, and defaulter tracking.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📢</div>
                <h3 class="feature-title">Notices & Communication</h3>
                <p class="feature-desc">Targeted broadcast messages, multi-channel alerts (SMS, push, email), and digital circulars.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✨</div>
                <h3 class="feature-title">AI Education Studio</h3>
                <p class="feature-desc">Generate lesson plans, question papers, and evaluate answer sheets instantly using our secure AI models.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Role-Based Security</h3>
                <p class="feature-desc">Strict tenant isolation with granular permissions for Admins, Teachers, Parents, and Accountants.</p>
            </div>
        </div>
        
        <div class="feature-more">
            <a href="#contact" class="btn btn-secondary">Request full feature list</a>
        </div>
    </section>

    <!-- 4. WHY US -->
    <section class="section-container why-section">
        <div class="why-grid">
            <div class="why-content">
                <h2>Built for modern schools, not legacy IT departments.</h2>
                <p>eschoolAI replaces outdated, fragmented school software with a unified, lightning-fast platform. We combine enterprise-grade security with a consumer-grade experience, empowering educators instead of slowing them down.</p>
                <ul class="check-list">
                    <li>
                        <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span><strong>Tenant-Isolated Security:</strong> Your data is strictly siloed. Cross-tenant leakage is architecturally impossible.</span>
                    </li>
                    <li>
                        <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span><strong>Context-Aware AI:</strong> Our RAG pipeline grounds AI responses exclusively in your school's uploaded textbooks and curriculum.</span>
                    </li>
                    <li>
                        <svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span><strong>Transparent Pricing:</strong> No hidden setup fees, no per-module paywalls. Pay only for what you use.</span>
                    </li>
                </ul>
            </div>
            <div class="why-visual">
                <div class="stat-box">
                    <div class="stat-value">99.9%</div>
                    <div class="stat-label">Uptime SLA</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">16+</div>
                    <div class="stat-label">Core Modules</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">&lt;50ms</div>
                    <div class="stat-label">Avg API Latency</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">6</div>
                    <div class="stat-label">AI Workflows</div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. PRICING -->
    <section id="pricing" class="section-container">
        <div class="section-header">
            <h2 class="section-title">Simple, transparent pricing.</h2>
            <p class="section-subtitle">Choose the plan that fits your school's size. Upgrade anytime as you grow.</p>
        </div>
        
        <div class="pricing-grid">
            @if(isset($plans) && $plans->count() > 0)
                @foreach($plans as $plan)
                <div class="pricing-card {{ $plan->is_popular ? 'popular' : '' }}">
                    @if($plan->is_popular)
                        <div class="popular-badge">Most Popular</div>
                    @endif
                    <h3 class="plan-name">{{ $plan->name }}</h3>
                    <div class="plan-price">
                        ${{ number_format($plan->price_monthly) }} <span class="plan-period">/mo</span>
                    </div>
                    <p class="plan-desc">{{ $plan->description ?? 'Complete school management for growing institutions.' }}</p>
                    
                    <ul class="plan-features">
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Up to {{ number_format($plan->student_limit) }} Students
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            {{ number_format($plan->ai_credit_quota) }} AI Generations / month
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            {{ $plan->storage_limit_gb }}GB Secure Cloud Storage
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            All 16 Core ERP Modules
                        </li>
                    </ul>
                    
                    <a href="/login?intent=register&plan={{ $plan->slug }}&utm_source=pricing" class="btn {{ $plan->is_popular ? 'btn-primary' : 'btn-secondary' }}" style="width: 100%; text-align:center;">Start {{ $plan->name }}</a>
                </div>
                @endforeach
            @else
                <div class="pricing-card">
                    <h3 class="plan-name">Starter</h3>
                    <div class="plan-price">$29 <span class="plan-period">/mo</span></div>
                    <p class="plan-desc">Perfect for small preschools and coaching centers.</p>
                    <ul class="plan-features">
                        <li>Up to 100 Students</li>
                        <li>500 AI Generations / mo</li>
                    </ul>
                    <a href="/login?intent=register" class="btn btn-secondary" style="width: 100%; text-align:center;">Start Starter</a>
                </div>
                <div class="pricing-card popular">
                    <div class="popular-badge">Most Popular</div>
                    <h3 class="plan-name">Professional</h3>
                    <div class="plan-price">$79 <span class="plan-period">/mo</span></div>
                    <p class="plan-desc">Ideal for K-12 schools aiming for digital transformation.</p>
                    <ul class="plan-features">
                        <li>Up to 1,000 Students</li>
                        <li>5,000 AI Generations / mo</li>
                    </ul>
                    <a href="/login?intent=register" class="btn btn-primary" style="width: 100%; text-align:center;">Start Professional</a>
                </div>
                <div class="pricing-card">
                    <h3 class="plan-name">Enterprise</h3>
                    <div class="plan-price">$199 <span class="plan-period">/mo</span></div>
                    <p class="plan-desc">For large institutions and university campuses.</p>
                    <ul class="plan-features">
                        <li>Unlimited Students</li>
                        <li>Unlimited AI Generations</li>
                    </ul>
                    <a href="/login?intent=register" class="btn btn-secondary" style="width: 100%; text-align:center;">Start Enterprise</a>
                </div>
            @endif
        </div>
        
        <div class="custom-banner">
            <div class="custom-banner-info">
                <h3>Multi-Campus or District?</h3>
                <p>We offer custom deployment options, dedicated account management, and volume discounts for large educational networks.</p>
            </div>
            <div>
                <!-- Form action points to a generic lead capture endpoint -->
                <a href="#contact" class="btn btn-primary">Get in touch</a>
            </div>
        </div>
    </section>

    <!-- 6. CONTACT -->
    <section id="contact" class="section-container">
        <div class="section-header">
            <h2 class="section-title">We're here to help.</h2>
            <p class="section-subtitle">Reach out to our education technology specialists for a personalized walkthrough or technical support.</p>
        </div>
        
        <div class="contact-grid">
            <div class="contact-card">
                <div class="contact-icon">📧</div>
                <h3 class="contact-title">Email Sales</h3>
                <p class="contact-detail">contact@greenfield.edu</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon">📱</div>
                <h3 class="contact-title">Phone Support</h3>
                <p class="contact-detail">+1 (234) 567-890</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon">📍</div>
                <h3 class="contact-title">Headquarters</h3>
                <p class="contact-detail">100 Academic Way, Metro City</p>
            </div>
        </div>
    </section>

    <!-- 8. FOOTER -->
    <footer class="landing-footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="/" class="brand-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--landing-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                    eschoolAI
                </a>
                <p>Empowering the next generation of educators with intelligent school management tools.</p>
            </div>
            
            <div>
                <h4 class="footer-heading">Product</h4>
                <ul class="footer-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="/login?intent=register">Start Trial</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="footer-heading">Legal</h4>
                <ul class="footer-links">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Refund Policy</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="footer-heading">Connect</h4>
                <ul class="footer-links">
                    <li><a href="#">Twitter (X)</a></li>
                    <li><a href="#">LinkedIn</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; {{ date('Y') }} eschoolAI. All rights reserved.
        </div>
    </footer>

    <script>
        // Simple mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', () => {
            const nav = document.querySelector('.nav-links');
            const actions = document.querySelector('.nav-actions');
            
            if (nav.style.display === 'flex') {
                nav.style.display = 'none';
                actions.style.display = 'none';
            } else {
                nav.style.display = 'flex';
                nav.style.flexDirection = 'column';
                nav.style.position = 'absolute';
                nav.style.top = '100%';
                nav.style.left = '0';
                nav.style.width = '100%';
                nav.style.background = 'rgba(11, 15, 25, 0.95)';
                nav.style.padding = '1rem';
                nav.style.borderBottom = '1px solid var(--landing-border)';
                
                actions.style.display = 'flex';
                actions.style.flexDirection = 'column';
                actions.style.position = 'absolute';
                actions.style.top = 'calc(100% + 150px)';
                actions.style.left = '0';
                actions.style.width = '100%';
                actions.style.background = 'rgba(11, 15, 25, 0.95)';
                actions.style.padding = '1rem';
                actions.style.borderBottom = '1px solid var(--landing-border)';
            }
        });
    </script>
</body>
</html>
