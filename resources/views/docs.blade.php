<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eschoolAI — Interactive API Documentation</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css">
    <link rel="stylesheet" href="/css/schoolos-design-system.css">
    <style>
        body { margin: 0; padding: 0; background: var(--bg-primary, #0f172a); font-family: var(--font-sans, system-ui); }
        .docs-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .swagger-ui .topbar { display: none !important; }
        .swagger-ui {
            background: #ffffff;
            border-radius: 12px;
            margin: 2rem auto;
            max-width: 1400px;
            padding: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
    <div class="docs-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                eS
            </div>
            <div>
                <h2 style="margin: 0; font-size: 1.125rem; color: #f8fafc;">eschoolAI — REST API Reference</h2>
                <p style="margin: 0; font-size: 0.75rem; color: #94a3b8;">Interactive OpenAPI 3.0 Documentation Specification</p>
            </div>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="/showcase" style="color: #cbd5e1; text-decoration: none; font-size: 0.875rem; padding: 0.5rem 1rem; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;">UI Showcase</a>
            <a href="/healthz" style="color: #10b981; text-decoration: none; font-size: 0.875rem; padding: 0.5rem 1rem; border: 1px solid rgba(16,185,129,0.3); border-radius: 6px;">Health: Up</a>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
    <script>
        window.onload = () => {
            window.ui = SwaggerUIBundle({
                url: '/api/v1/openapi.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>
