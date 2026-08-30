<?php

$mdPath = __DIR__ . '/DOCUMENTATION.md';
if (!file_exists($mdPath)) {
    die("DOCUMENTATION.md not found!\n");
}

$markdown = file_get_contents($mdPath);

// Function to convert Markdown to styled HTML
function parseMarkdownToHtml($text) {
    $lines = explode("\n", $text);
    $html = '';
    $inCodeBlock = false;
    $codeContent = '';
    $inTable = false;
    $tableRows = [];
    $inList = false;
    $listType = 'ul';

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Code block toggle
        if (str_starts_with($trimmed, '```')) {
            if ($inCodeBlock) {
                $html .= '<pre><code>' . htmlspecialchars($codeContent) . '</code></pre>' . "\n";
                $codeContent = '';
                $inCodeBlock = false;
            } else {
                if ($inList) { $html .= "</$listType>\n"; $inList = false; }
                if ($inTable) { $html .= renderTable($tableRows); $tableRows = []; $inTable = false; }
                $inCodeBlock = true;
            }
            continue;
        }

        if ($inCodeBlock) {
            $codeContent .= $line . "\n";
            continue;
        }

        // Table rows
        if (str_starts_with($trimmed, '|')) {
            if ($inList) { $html .= "</$listType>\n"; $inList = false; }
            if (preg_match('/^\|[\s\-:|]+\|$/', $trimmed)) {
                // Table divider line, ignore
                continue;
            }
            $inTable = true;
            $tableRows[] = $trimmed;
            continue;
        } else if ($inTable) {
            $html .= renderTable($tableRows);
            $tableRows = [];
            $inTable = false;
        }

        // Empty line
        if ($trimmed === '') {
            if ($inList) { $html .= "</$listType>\n"; $inList = false; }
            continue;
        }

        // Horizontal rule
        if ($trimmed === '---' || $trimmed === '***') {
            if ($inList) { $html .= "</$listType>\n"; $inList = false; }
            $html .= '<hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;">' . "\n";
            continue;
        }

        // Headings
        if (preg_match('/^(#{1,6})\s+(.*)$/', $trimmed, $matches)) {
            if ($inList) { $html .= "</$listType>\n"; $inList = false; }
            $level = strlen($matches[1]);
            $title = parseInline($matches[2]);
            $html .= "<h{$level}>{$title}</h{$level}>\n";
            continue;
        }

        // Blockquotes
        if (str_starts_with($trimmed, '>')) {
            if ($inList) { $html .= "</$listType>\n"; $inList = false; }
            $quoteText = parseInline(trim(substr($trimmed, 1)));
            $html .= "<blockquote>{$quoteText}</blockquote>\n";
            continue;
        }

        // Lists (unordered)
        if (preg_match('/^[-*•]\s+(.*)$/', $trimmed, $matches)) {
            if (!$inList || $listType !== 'ul') {
                if ($inList) $html .= "</$listType>\n";
                $html .= "<ul>\n";
                $inList = true;
                $listType = 'ul';
            }
            $html .= "<li>" . parseInline($matches[1]) . "</li>\n";
            continue;
        }

        // Lists (ordered)
        if (preg_match('/^\d+\.\s+(.*)$/', $trimmed, $matches)) {
            if (!$inList || $listType !== 'ol') {
                if ($inList) $html .= "</$listType>\n";
                $html .= "<ol>\n";
                $inList = true;
                $listType = 'ol';
            }
            $html .= "<li>" . parseInline($matches[1]) . "</li>\n";
            continue;
        }

        if ($inList) {
            $html .= "</$listType>\n";
            $inList = false;
        }

        // Regular paragraph
        $html .= "<p>" . parseInline($line) . "</p>\n";
    }

    if ($inList) { $html .= "</$listType>\n"; }
    if ($inTable) { $html .= renderTable($tableRows); }
    if ($inCodeBlock) { $html .= '<pre><code>' . htmlspecialchars($codeContent) . '</code></pre>' . "\n"; }

    return $html;
}

function parseInline($text) {
    // Bold
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    // Italic
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    // Inline code
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // Links
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" style="color: #ff5b37; text-decoration: underline;">$1</a>', $text);
    return $text;
}

function renderTable($rows) {
    if (empty($rows)) return '';
    $out = "<table>\n";
    $isHeader = true;
    foreach ($rows as $row) {
        $cols = array_map('trim', explode('|', trim($row, '|')));
        $tag = $isHeader ? 'th' : 'td';
        $out .= "  <tr>\n";
        foreach ($cols as $col) {
            $out .= "    <{$tag}>" . parseInline($col) . "</{$tag}>\n";
        }
        $out .= "  </tr>\n";
        $isHeader = false;
    }
    $out .= "</table>\n";
    return $out;
}

$bodyHtml = parseMarkdownToHtml($markdown);

$fullHtml = '<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<title>eschoolAI Master Platform Documentation</title>
<!--[if gte mso 9]>
<xml>
<w:WordDocument>
<w:View>Print</w:View>
<w:Zoom>100</w:Zoom>
<w:DoNotOptimizeForBrowser/>
</w:WordDocument>
</xml>
<![endif]-->
<style>
@page {
    size: 8.5in 11.0in;
    margin: 1.0in;
    mso-header-margin: .5in;
    mso-footer-margin: .5in;
}
body {
    font-family: "Segoe UI", "Calibri", Arial, sans-serif;
    font-size: 11pt;
    line-height: 1.6;
    color: #1e293b;
    background: #ffffff;
}
.cover-page {
    text-align: center;
    padding: 80px 20px;
    border-bottom: 2px solid #ff5b37;
    margin-bottom: 40px;
    page-break-after: always;
}
.brand-pill {
    display: inline-block;
    background: linear-gradient(135deg, #ff5b37, #8b5cf6);
    color: #ffffff;
    font-weight: 800;
    font-size: 14pt;
    padding: 10px 22px;
    border-radius: 8px;
    margin-bottom: 24px;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.cover-title {
    font-size: 30pt;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 12px 0;
    line-height: 1.2;
}
.cover-sub {
    font-size: 14pt;
    color: #64748b;
    margin: 0 0 35px 0;
}
.meta-card {
    margin: 30px auto;
    max-width: 480px;
    text-align: left;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 20px 24px;
    font-size: 10.5pt;
}
.meta-row {
    margin-bottom: 8px;
}
.meta-row:last-child {
    margin-bottom: 0;
}
.meta-label {
    font-weight: bold;
    color: #334155;
    width: 140px;
    display: inline-block;
}
h1 {
    font-size: 19pt;
    font-weight: 800;
    color: #0f172a;
    border-bottom: 2.5px solid #ff5b37;
    padding-bottom: 8px;
    margin-top: 36px;
    margin-bottom: 16px;
}
h2 {
    font-size: 14pt;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 5px;
    margin-top: 28px;
    margin-bottom: 12px;
}
h3 {
    font-size: 12pt;
    font-weight: 700;
    color: #334155;
    margin-top: 20px;
    margin-bottom: 8px;
}
p {
    margin-top: 0;
    margin-bottom: 12px;
    font-size: 11pt;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 16px 0 24px 0;
    font-size: 10pt;
}
th {
    background-color: #f1f5f9;
    color: #0f172a;
    font-weight: 700;
    border: 1px solid #cbd5e1;
    padding: 9px 12px;
    text-align: left;
}
td {
    border: 1px solid #cbd5e1;
    padding: 8px 12px;
    vertical-align: top;
}
tr:nth-child(even) td {
    background-color: #f8fafc;
}
pre {
    background-color: #0f172a;
    color: #f8fafc;
    padding: 14px 18px;
    border-radius: 8px;
    font-family: "Consolas", "Courier New", monospace;
    font-size: 9.5pt;
    line-height: 1.45;
    overflow-x: auto;
    white-space: pre-wrap;
    margin: 16px 0;
}
code {
    font-family: "Consolas", "Courier New", monospace;
    font-size: 9.5pt;
    background-color: #f1f5f9;
    color: #b91c1c;
    padding: 2px 5px;
    border-radius: 4px;
}
pre code {
    background-color: transparent;
    color: #f8fafc;
    padding: 0;
}
blockquote {
    border-left: 4px solid #ff5b37;
    margin: 16px 0;
    padding: 10px 18px;
    background-color: #fff7ed;
    color: #9a3412;
    font-style: italic;
    border-radius: 0 6px 6px 0;
}
ul, ol {
    margin-top: 0;
    margin-bottom: 14px;
    padding-left: 24px;
}
li {
    margin-bottom: 6px;
}
</style>
</head>
<body>

<div class="cover-page">
    <div class="brand-pill">eschoolAI Enterprise Platform</div>
    <div class="cover-title">Master Project Documentation</div>
    <div class="cover-sub">Comprehensive End-User Manual & Developer Architecture Guide (A to Z)</div>
    
    <div class="meta-card">
        <div class="meta-row"><span class="meta-label">Project Name:</span> <strong>eschoolAI Multi-Tenant SaaS</strong></div>
        <div class="meta-row"><span class="meta-label">Version:</span> 1.0.0-PROD</div>
        <div class="meta-row"><span class="meta-label">Architecture:</span> Multi-Tenant RESTful + Realtime + Vector RAG</div>
        <div class="meta-row"><span class="meta-label">Backend Stack:</span> Laravel 13, PHP 8.3+, MySQL, Redis</div>
        <div class="meta-row"><span class="meta-label">Vector Database:</span> Qdrant Vector Engine (Tenant-Scoped)</div>
        <div class="meta-row"><span class="meta-label">Test Suite:</span> 57/57 Tests Passing (303 Assertions)</div>
        <div class="meta-row"><span class="meta-label">Date Generated:</span> ' . date("F d, Y") . '</div>
        <div class="meta-row"><span class="meta-label">Repository:</span> https://github.com/AmitBMakwana/eschoolAI</div>
        <div class="meta-row"><span class="meta-label">Release Status:</span> Production Ready</div>
    </div>
</div>

' . $bodyHtml . '

</body>
</html>';

$bom = "\xEF\xBB\xBF";
file_put_contents(__DIR__ . '/eschoolAI_Master_Documentation.doc', $bom . $fullHtml);

if (!is_dir(__DIR__ . '/public/docs')) {
    mkdir(__DIR__ . '/public/docs', 0777, true);
}
file_put_contents(__DIR__ . '/public/docs/eschoolAI_Master_Documentation.doc', $bom . $fullHtml);

echo "SUCCESS: eschoolAI_Master_Documentation.doc generated successfully in root and public/docs!\n";
